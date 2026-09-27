<?php

namespace TasmoAdmin\Health;

use TasmoAdmin\Device;
use TasmoAdmin\Mqtt\MqttClientInterface;
use TasmoAdmin\Sonoff;

final class HealthCollector
{
    private \Closure $clock;

    /**
     * Topics devices report over HTTP, for devices without one configured in
     * TasmoAdmin. Seeded from the database so a restart can match MQTT at once.
     *
     * @var array<int|string, string>
     */
    private array $reportedTopics = [];

    /** @var array<int|string, bool> */
    private array $mqttEnabled = [];

    public function __construct(
        private HealthRepository $repo,
        private Sonoff $sonoff,
        private int $graceSeconds,
        ?callable $clock = null,
    ) {
        $this->clock = $clock ?? static fn () => (int) microtime(true);

        foreach ($this->repo->all() as $row) {
            if (!empty($row['mqtt_topic'])) {
                $this->reportedTopics[(string) $row['device_id']] = (string) $row['mqtt_topic'];
            }
        }
    }

    public function pollHttpDevice(Device $device, int $now): void
    {
        $deviceId = (string) $device->id;
        $status = $this->sonoff->getAllStatus($device);

        $http_up = !isset($status->ERROR) ? 1 : 0;

        $previous = $this->repo->get($deviceId);
        $row = $this->mergePrevious($deviceId, $previous);

        $row['http_up'] = $http_up;
        if ($http_up) {
            $row['last_http_ok'] = $now;
            $this->learnMqttSettings($deviceId, $status);

            if (isset($status->StatusSTS->Wifi->Signal)) {
                $row['signal'] = (int) $status->StatusSTS->Wifi->Signal;
            }
            if (isset($status->StatusSTS->Wifi->RSSI)) {
                $row['rssi'] = (int) $status->StatusSTS->Wifi->RSSI;
            }
        }

        $row['mqtt_topic'] = $this->effectiveTopic($device);
        $row['mqtt_expected'] = '' !== $row['mqtt_topic'] && ($this->mqttEnabled[$deviceId] ?? true) ? 1 : 0;

        $this->recomputeAndSave($row, $now, $previous['state'] ?? HealthState::UNKNOWN);
    }

    /**
     * @param Device[] $devices
     */
    public function handleMqttMessage(string $topic, string $payload, array $devices, int $now): void
    {
        if (!TopicMatcher::isLwtTopic($topic) && !TopicMatcher::isStateTopic($topic)) {
            return;
        }

        $device = TopicMatcher::matchDevice($topic, $devices, $this->reportedTopics);
        if (!$device instanceof Device) {
            return;
        }

        $deviceId = (string) $device->id;
        $previous = $this->repo->get($deviceId);
        $row = $this->mergePrevious($deviceId, $previous);
        // A matched message proves the device talks MQTT to this broker.
        $row['mqtt_topic'] = $this->effectiveTopic($device);
        $row['mqtt_expected'] = 1;

        if (TopicMatcher::isLwtTopic($topic)) {
            $online = TopicMatcher::lwtOnline($payload);
            $row['mqtt_up'] = $online ? 1 : 0;
            if ($online) {
                $row['last_mqtt_ok'] = $now;
            }
        } else {
            $row['mqtt_up'] = 1;
            $row['last_mqtt_ok'] = $now;
            $state = json_decode($payload);
            if (isset($state->Wifi->Signal)) {
                $row['signal'] = (int) $state->Wifi->Signal;
            }
            if (isset($state->Wifi->RSSI)) {
                $row['rssi'] = (int) $state->Wifi->RSSI;
            }
        }

        $this->recomputeAndSave($row, $now, $previous['state'] ?? HealthState::UNKNOWN);
    }

    public function runOnce(
        MqttClientInterface $client,
        string $subscription,
        int $mqttDrainSeconds
    ): void {
        $devices = $this->sonoff->getDevices();
        $now = ($this->clock)();

        foreach ($devices as $device) {
            $this->pollHttpDevice($device, ($this->clock)());
        }

        $loopStartedAt = microtime(true);
        $client->subscribe($subscription, function (string $topic, string $message) use ($devices): void {
            $this->handleMqttMessage($topic, $message, $devices, ($this->clock)());
        });

        $deadline = $now + max($mqttDrainSeconds, 1);
        while (($this->clock)() < $deadline) {
            $client->loopOnce($loopStartedAt);
            usleep(50_000);
        }
    }

    public function run(
        MqttClientInterface $client,
        string $subscription,
        int $httpPollInterval
    ): void {
        $loopStartedAt = microtime(true);

        // Poll HTTP first so reported topics are known before retained LWT
        // messages arrive on subscribe.
        $devices = $this->sonoff->getDevices();
        $this->pollAll($client, $devices, $loopStartedAt);
        $lastPoll = ($this->clock)();

        $client->subscribe($subscription, function (string $topic, string $message) use (&$devices): void {
            $this->handleMqttMessage($topic, $message, $devices, ($this->clock)());
        });

        while ($this->shouldRun()) {
            $now = ($this->clock)();
            if (($now - $lastPoll) >= $httpPollInterval) {
                $devices = $this->sonoff->getDevices();
                $this->pollAll($client, $devices, $loopStartedAt);
                $lastPoll = $now;
            }

            $client->loopOnce($loopStartedAt);
            usleep(100_000);
        }
    }

    private function shouldRun(): bool
    {
        return true;
    }

    /**
     * @param Device[] $devices
     */
    private function pollAll(MqttClientInterface $client, array $devices, float $loopStartedAt): void
    {
        foreach ($devices as $device) {
            $this->pollHttpDevice($device, ($this->clock)());
            // Keep broker keepalive serviced during sweep.
            $client->loopOnce($loopStartedAt);
        }
    }

    /**
     * Status 0 carries the device topic and, only when MQTT is enabled, StatusMQT.
     */
    private function learnMqttSettings(string $deviceId, \stdClass $status): void
    {
        if (!isset($status->Status) || !is_object($status->Status)) {
            return;
        }

        $topic = trim((string) ($status->Status->Topic ?? ''));
        // Unexpanded placeholders (e.g. tasmota_%06X) cannot match a real topic.
        if ('' !== $topic && !str_contains($topic, '%')) {
            $this->reportedTopics[$deviceId] = $topic;
        }

        $this->mqttEnabled[$deviceId] = isset($status->StatusMQT)
            && '' !== trim((string) ($status->StatusMQT->MqttHost ?? ''));
    }

    private function effectiveTopic(Device $device): string
    {
        $configured = trim($device->mqttTopic);

        return '' !== $configured ? $configured : ($this->reportedTopics[(string) $device->id] ?? '');
    }

    private function mergePrevious(string $deviceId, ?array $previous): array
    {
        return [
            'device_id' => $deviceId,
            'http_up' => isset($previous['http_up']) ? (int) $previous['http_up'] : null,
            'mqtt_up' => isset($previous['mqtt_up']) ? (int) $previous['mqtt_up'] : null,
            'last_http_ok' => isset($previous['last_http_ok']) ? (int) $previous['last_http_ok'] : null,
            'last_mqtt_ok' => isset($previous['last_mqtt_ok']) ? (int) $previous['last_mqtt_ok'] : null,
            'last_seen' => isset($previous['last_seen']) ? (int) $previous['last_seen'] : null,
            'rssi' => isset($previous['rssi']) ? (int) $previous['rssi'] : null,
            'signal' => isset($previous['signal']) ? (int) $previous['signal'] : null,
            'mqtt_topic' => $previous['mqtt_topic'] ?? null,
            'mqtt_expected' => isset($previous['mqtt_expected']) ? (int) $previous['mqtt_expected'] : null,
        ];
    }

    private function recomputeAndSave(array $row, int $now, string $previousState): void
    {
        $httpUp = null;
        if (null !== $row['http_up']) {
            $httpUp = (bool) $row['http_up'];
        }

        $mqttUp = null;
        if (null !== $row['mqtt_up']) {
            $mqttUp = (bool) $row['mqtt_up'];
        }

        $row['last_seen'] = max(
            (int) ($row['last_http_ok'] ?? 0),
            (int) ($row['last_mqtt_ok'] ?? 0),
        ) ?: null;

        $row['state'] = HealthState::resolve(
            httpUp: $httpUp,
            mqttUp: $mqttUp,
            lastSeen: $row['last_seen'],
            now: $now,
            graceSeconds: $this->graceSeconds,
            previousState: $previousState,
            mqttExpected: 0 !== ($row['mqtt_expected'] ?? 1),
        );

        $row['updated_at'] = $now;

        $this->repo->upsert($row);
    }
}
