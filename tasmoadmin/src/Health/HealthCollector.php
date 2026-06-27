<?php

namespace TasmoAdmin\Health;

use TasmoAdmin\Device;
use TasmoAdmin\Sonoff;

final class HealthCollector
{
    private ?\Closure $clock;

    public function __construct(
        private HealthRepository $repo,
        private Sonoff $sonoff,
        private int $graceSeconds,
        ?callable $clock = null,
    ) {
        $this->clock = $clock ?? static fn() => (int) microtime(true);
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

            if (isset($status->StatusSTS->Wifi->Signal)) {
                $row['signal'] = (int) $status->StatusSTS->Wifi->Signal;
            }
            if (isset($status->StatusSTS->Wifi->RSSI)) {
                $row['rssi'] = (int) $status->StatusSTS->Wifi->RSSI;
            }
        }

        $this->recomputeAndSave($row, $now, $previous['state'] ?? HealthState::UNKNOWN);
    }

    /**
     * @param Device[] $devices
     */
    public function handleMqttMessage(string $topic, string $payload, array $devices, int $now): void
    {
        if (!TopicMatcher::isLwtTopic($topic)) {
            return;
        }

        $device = TopicMatcher::matchDevice($topic, $devices);
        if (!$device instanceof Device) {
            return;
        }

        $deviceId = (string) $device->id;
        $previous = $this->repo->get($deviceId);
        $row = $this->mergePrevious($deviceId, $previous);

        $online = TopicMatcher::lwtOnline($payload);
        $row['mqtt_up'] = $online ? 1 : 0;
        if ($online) {
            $row['last_mqtt_ok'] = $now;
        }

        $this->recomputeAndSave($row, $now, $previous['state'] ?? HealthState::UNKNOWN);
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
        ];
    }

    private function recomputeAndSave(array $row, int $now, string $previousState): void
    {
        $httpUp = null;
        if ($row['http_up'] !== null) {
            $httpUp = (bool) $row['http_up'];
        }

        $mqttUp = null;
        if ($row['mqtt_up'] !== null) {
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
        );

        $row['updated_at'] = $now;

        $this->repo->upsert($row);
    }

    public function runOnce(
        \TasmoAdmin\Mqtt\MqttClientInterface $client,
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
        \TasmoAdmin\Mqtt\MqttClientInterface $client,
        string $subscription,
        int $httpPollInterval
    ): void {
        $devices = $this->sonoff->getDevices();
        $loopStartedAt = microtime(true);

        $client->subscribe($subscription, function (string $topic, string $message) use (&$devices): void {
            $this->handleMqttMessage($topic, $message, $devices, ($this->clock)());
        });

        $lastPoll = 0;
        while (true) {
            $now = ($this->clock)();
            if (($now - $lastPoll) >= $httpPollInterval) {
                $devices = $this->sonoff->getDevices();
                foreach ($devices as $device) {
                    $this->pollHttpDevice($device, ($this->clock)());
                    // Keep broker keepalive serviced during sweep.
                    $client->loopOnce($loopStartedAt);
                }
                $lastPoll = $now;
            }

            $client->loopOnce($loopStartedAt);
            usleep(100_000);
        }
    }
}
