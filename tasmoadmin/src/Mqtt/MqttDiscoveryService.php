<?php

namespace TasmoAdmin\Mqtt;

use TasmoAdmin\Device;
use TasmoAdmin\DeviceRepository;
use TasmoAdmin\Tasmota\ResponseParser;

class MqttDiscoveryService
{
    /**
     * Tasmota 9.5.0 and later answer `STATUS0` with one combined `STATUS0` message. Older
     * firmware ignores the index and answers payload `0` with one message per section, of
     * which `STATUS` (FriendlyName), `STATUS5` (StatusNET) and `STATUS11` (StatusSTS) are needed.
     */
    private const STATUS_REPLY_SUFFIXES = ['STATUS0', 'STATUS', 'STATUS5', 'STATUS11'];

    public function __construct(
        private DeviceRepository $deviceRepository,
        private ResponseParser $responseParser,
        private MqttClientFactoryInterface $mqttClientFactory,
        private TimeProviderInterface $timeProvider
    ) {}

    public function scan(MqttDiscoveryRequest $request): MqttDiscoveryResult
    {
        $client = $this->mqttClientFactory->create($request->host, $request->port);
        $discoveredTopics = [];
        $publishedTopics = [];
        $statusPayloads = [];
        $loopStartedAt = $this->timeProvider->now();

        $handleMessage = function (string $topic, string $message) use (
            &$discoveredTopics,
            &$publishedTopics,
            &$statusPayloads,
            $request,
            $client
        ): void {
            $statusTopic = $this->extractStatusTopic($topic, $request->statPrefix);
            if (null !== $statusTopic) {
                $statusPayloads[$statusTopic] = $this->mergeStatusPayload($statusPayloads[$statusTopic] ?? null, $message);

                return;
            }

            $mqttTopic = $this->extractDiscoveryTopic($topic, $request->telePrefix);
            if (null === $mqttTopic) {
                return;
            }

            $discoveredTopics[$mqttTopic] = true;
            if (isset($publishedTopics[$mqttTopic])) {
                return;
            }

            $client->publish($this->buildTopic($request->commandPrefix, $mqttTopic, 'STATUS0'), '0');
            $publishedTopics[$mqttTopic] = true;
        };

        try {
            $client->connect($request->username, $request->password, $request->timeoutSeconds);

            foreach ($request->subscriptionFilters as $subscriptionFilter) {
                $client->subscribe($subscriptionFilter, $handleMessage);
            }

            $client->subscribe($this->buildPrefixWildcardTopic($request->statPrefix), $handleMessage);

            $deadline = $this->timeProvider->now() + max($request->timeoutSeconds, 1);
            while ($this->timeProvider->now() < $deadline) {
                $client->loopOnce($loopStartedAt);
                $this->timeProvider->sleep(50_000);
            }
        } finally {
            $client->disconnect();
        }

        return $this->buildResult(array_keys($discoveredTopics), $statusPayloads, $request);
    }

    /**
     * @param string[]              $discoveredTopics
     * @param array<string, string> $statusPayloads
     */
    private function buildResult(array $discoveredTopics, array $statusPayloads, MqttDiscoveryRequest $request): MqttDiscoveryResult
    {
        $result = new MqttDiscoveryResult();
        $claimedAddresses = [];

        foreach ($discoveredTopics as $mqttTopic) {
            if ($this->deviceRepository->isMqttTopicAmbiguous($mqttTopic)) {
                $result->conflicts[] = [
                    'mqttTopic' => $mqttTopic,
                    'reason' => 'existing-topic-duplicate',
                ];

                continue;
            }

            if (!array_key_exists($mqttTopic, $statusPayloads)) {
                $result->offlineTopics[] = [
                    'mqttTopic' => $mqttTopic,
                    'reason' => 'missing-status0-response',
                ];

                continue;
            }

            $status = $this->responseParser->processResult($statusPayloads[$mqttTopic]);
            if (isset($status->ERROR) || empty($status->StatusNET->IPAddress)) {
                $result->offlineTopics[] = [
                    'mqttTopic' => $mqttTopic,
                    'reason' => 'invalid-status0-response',
                ];

                continue;
            }

            $addressKey = $this->buildAddressKey((string) $status->StatusNET->IPAddress, $request->httpPort);
            if (isset($claimedAddresses[$addressKey]) && $claimedAddresses[$addressKey] !== $mqttTopic) {
                $result->conflicts[] = [
                    'mqttTopic' => $mqttTopic,
                    'reason' => 'address-already-claimed',
                ];

                continue;
            }

            $devices = $this->deviceRepository->getDevices();
            $existingDevice = $this->deviceRepository->getDeviceByMqttTopic($mqttTopic);
            if ($existingDevice instanceof Device) {
                $addressMatches = $this->findAddressMatches($devices, (string) $status->StatusNET->IPAddress, $request->httpPort);
                $conflictingAddressMatches = array_values(array_filter(
                    $addressMatches,
                    static fn (Device $device): bool => $device->id !== $existingDevice->id
                ));
                if ([] !== $conflictingAddressMatches) {
                    $result->conflicts[] = [
                        'mqttTopic' => $mqttTopic,
                        'reason' => 'topic-collides-with-other-device-address',
                    ];

                    continue;
                }

                $result->updatedDevices[] = $this->refreshExistingDevice($existingDevice, $mqttTopic, $status, false, $request);
                $claimedAddresses[$addressKey] = $mqttTopic;

                continue;
            }

            $legacyMatches = $this->findAddressMatches($devices, (string) $status->StatusNET->IPAddress, $request->httpPort);
            if (count($legacyMatches) > 1) {
                $result->conflicts[] = [
                    'mqttTopic' => $mqttTopic,
                    'reason' => 'legacy-address-ambiguous',
                ];

                continue;
            }

            if (1 === count($legacyMatches)) {
                $result->updatedDevices[] = $this->refreshExistingDevice($legacyMatches[0], $mqttTopic, $status, true, $request);
                $claimedAddresses[$addressKey] = $mqttTopic;

                continue;
            }

            $status->TasmoAdminMqttTopic = $mqttTopic;
            $status->TasmoAdminDevicePort = $request->httpPort;
            $result->newDevices[] = $status;
            $claimedAddresses[$addressKey] = $mqttTopic;
        }

        return $result;
    }

    /**
     * @param Device[] $devices
     *
     * @return Device[]
     */
    private function findAddressMatches(array $devices, string $ip, int $port): array
    {
        $matches = [];
        foreach ($devices as $device) {
            if ($device->ip === $ip && $device->port === $port) {
                $matches[] = $device;
            }
        }

        return $matches;
    }

    /**
     * @return array<string, mixed>
     */
    private function refreshExistingDevice(
        Device $device,
        string $mqttTopic,
        \stdClass $status,
        bool $backfilledTopic,
        MqttDiscoveryRequest $request
    ): array {
        $oldIp = $device->ip;
        $device->ip = (string) $status->StatusNET->IPAddress;
        $device->mqttTopic = $mqttTopic;
        $this->deviceRepository->updateDevice($device);

        return [
            'deviceId' => $device->id,
            'mqttTopic' => $mqttTopic,
            'oldIp' => $oldIp,
            'newIp' => $device->ip,
            'port' => $device->port,
            'backfilledTopic' => $backfilledTopic ? '1' : '0',
            'name' => $device->getName(),
        ];
    }

    private function extractDiscoveryTopic(string $topic, string $telePrefix): ?string
    {
        return $this->extractTopicForPrefix($topic, $telePrefix);
    }

    private function extractStatusTopic(string $topic, string $statPrefix): ?string
    {
        return $this->extractTopicForPrefix($topic, $statPrefix, self::STATUS_REPLY_SUFFIXES);
    }

    /**
     * @param null|string[] $allowedSuffixes
     */
    private function extractTopicForPrefix(string $topic, string $prefix, ?array $allowedSuffixes = null): ?string
    {
        $topicParts = $this->splitTopic($topic);
        $prefixParts = $this->splitTopic($prefix);
        if (count($topicParts) <= count($prefixParts)) {
            return null;
        }

        foreach ($prefixParts as $index => $prefixPart) {
            if (!isset($topicParts[$index]) || $topicParts[$index] !== $prefixPart) {
                return null;
            }
        }

        $suffix = strtoupper((string) array_pop($topicParts));
        if (null !== $allowedSuffixes && !in_array($suffix, $allowedSuffixes, true)) {
            return null;
        }

        $mqttTopicParts = array_slice($topicParts, count($prefixParts));
        $mqttTopic = trim(implode('/', $mqttTopicParts));

        return '' !== $mqttTopic ? $mqttTopic : null;
    }

    private function mergeStatusPayload(?string $existing, string $incoming): string
    {
        if (null === $existing) {
            return $incoming;
        }

        $existingData = json_decode($existing);
        $incomingData = json_decode($incoming);
        if (!$existingData instanceof \stdClass || !$incomingData instanceof \stdClass) {
            return $existing;
        }

        return (string) json_encode(
            (object) array_merge((array) $existingData, (array) $incomingData),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }

    private function buildTopic(string $prefix, string $mqttTopic, string $suffix): string
    {
        return sprintf('%s/%s/%s', trim($prefix, '/'), trim($mqttTopic, '/'), trim($suffix, '/'));
    }

    private function buildPrefixWildcardTopic(string $prefix): string
    {
        return sprintf('%s/#', trim($prefix, '/'));
    }

    private function buildAddressKey(string $ip, int $port): string
    {
        return sprintf('%s:%d', $ip, $port);
    }

    /**
     * @return string[]
     */
    private function splitTopic(string $topic): array
    {
        return array_values(array_filter(explode('/', trim($topic, '/')), static fn (string $part): bool => '' !== $part));
    }
}
