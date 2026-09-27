<?php

namespace Tests\TasmoAdmin\Health;

use PHPUnit\Framework\TestCase;
use TasmoAdmin\Device;
use TasmoAdmin\Health\HealthCollector;
use TasmoAdmin\Health\HealthRepository;
use TasmoAdmin\Health\HealthState;
use TasmoAdmin\Sonoff;

final class HealthCollectorTest extends TestCase
{
    private string $dir;
    private HealthRepository $repo;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir().'/ta-coll-'.bin2hex(random_bytes(4));
        mkdir($this->dir, 0o777, true);
        $this->repo = new HealthRepository($this->dir.'/health.db');
        $this->repo->ensureSchema();
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->dir.'/*') ?: []);
        @rmdir($this->dir);
    }

    public function testHttpPollRecordsReachableDevice(): void
    {
        $status = new \stdClass();
        $status->StatusSTS = new \stdClass();
        $status->StatusSTS->Wifi = new \stdClass();
        $status->StatusSTS->Wifi->RSSI = 72;
        $status->StatusSTS->Wifi->Signal = -55;

        $sonoff = $this->createMock(Sonoff::class);
        $sonoff->method('getAllStatus')->willReturn($status);

        $collector = new HealthCollector($this->repo, $sonoff, 180);
        $collector->pollHttpDevice($this->device(1), 1000);

        $row = $this->repo->get('1');
        self::assertSame(1, (int) $row['http_up']);
        self::assertSame(-55, (int) $row['signal']);
        self::assertSame(1000, (int) $row['last_http_ok']);
        // mqtt unknown so far -> degraded_mqtt (http up, mqtt down/unknown treated as down)
        self::assertSame(HealthState::DEGRADED_MQTT, $row['state']);
    }

    public function testHttpPollRecordsUnreachableDevice(): void
    {
        $status = new \stdClass();
        $status->ERROR = 'cURL error 28';

        $sonoff = $this->createMock(Sonoff::class);
        $sonoff->method('getAllStatus')->willReturn($status);

        $collector = new HealthCollector($this->repo, $sonoff, 180);
        $collector->pollHttpDevice($this->device(1), 1000);

        $row = $this->repo->get('1');
        self::assertSame(0, (int) $row['http_up']);
        self::assertSame(HealthState::OFFLINE, $row['state']);
    }

    public function testMqttLwtOnlineThenHttpDownIsDegradedHttp(): void
    {
        $sonoff = $this->createMock(Sonoff::class);
        $collector = new HealthCollector($this->repo, $sonoff, 180);

        $collector->handleMqttMessage('tele/kitchen/LWT', 'Online', [$this->device(1)], 1000);
        $row = $this->repo->get('1');
        self::assertSame(1, (int) $row['mqtt_up']);

        // Now an HTTP failure arrives later; mqtt still recently up -> degraded_http
        $err = new \stdClass();
        $err->ERROR = 'timeout';
        $sonoff->method('getAllStatus')->willReturn($err);
        $collector->pollHttpDevice($this->device(1), 1100);

        $row = $this->repo->get('1');
        self::assertSame(HealthState::DEGRADED_HTTP, $row['state']);
    }

    public function testMqttStateMessageMarksMqttUpAndStoresWifiMetrics(): void
    {
        $sonoff = $this->createMock(Sonoff::class);
        $collector = new HealthCollector($this->repo, $sonoff, 180);
        $collector->handleMqttMessage('tele/kitchen/STATE', '{"Wifi":{"RSSI":83,"Signal":-49}}', [$this->device(1)], 1000);

        $row = $this->repo->get('1');

        self::assertSame(1, (int) $row['mqtt_up']);
        self::assertSame(1000, (int) $row['last_mqtt_ok']);
        self::assertSame(83, (int) $row['rssi']);
        self::assertSame(-49, (int) $row['signal']);
        self::assertSame(HealthState::DEGRADED_HTTP, $row['state']);
    }

    public function testUnrelatedMqttMessageIsIgnored(): void
    {
        $sonoff = $this->createMock(Sonoff::class);
        $collector = new HealthCollector($this->repo, $sonoff, 180);
        $collector->handleMqttMessage('tele/kitchen/SENSOR', '{}', [$this->device(1)], 1000);

        self::assertNull($this->repo->get('1'));
    }

    /**
     * Regression: LWT-Offline MUST NOT advance last_seen to $now.
     *
     * Scenario:
     *   - LWT 'Online' at now=100  → last_mqtt_ok=100, last_seen=100.
     *   - LWT 'Offline' at now=500 → mqtt_up=0, last_mqtt_ok stays 100.
     *   - http_up is null (no HTTP poll ever occurred).
     *   - Grace period = 180 s.  now - last_seen = 500 - 100 = 400 > 180 → OFFLINE.
     *
     * Against the old eager code ($row['last_seen']=$now inside handleMqttMessage)
     * last_seen would be stamped to 500 on LWT-Offline, so now-last_seen = 0 < 180
     * and the device would stay in the previous state (degraded_http) rather than
     * transitioning to OFFLINE — the grace-period expiry could never fire.
     */
    public function testLwtOfflineDoesNotAdvanceLastSeenAndExpiresGrace(): void
    {
        $sonoff = $this->createMock(Sonoff::class);
        $collector = new HealthCollector($this->repo, $sonoff, 180);

        // Step 1: MQTT comes online at now=100.
        $collector->handleMqttMessage('tele/kitchen/LWT', 'Online', [$this->device(1, 'kitchen')], 100);
        $row = $this->repo->get('1');
        self::assertSame(100, (int) $row['last_mqtt_ok'], 'last_mqtt_ok must be 100 after Online LWT');
        self::assertSame(100, (int) $row['last_seen'], 'last_seen must be 100 after Online LWT');

        // Step 2: LWT 'Offline' arrives at now=500.
        // http_up is still null (no HTTP poll), mqtt_up becomes 0.
        // last_seen must stay at 100 (from last_mqtt_ok), NOT jump to 500.
        $collector->handleMqttMessage('tele/kitchen/LWT', 'Offline', [$this->device(1, 'kitchen')], 500);

        $row = $this->repo->get('1');

        // last_seen must reflect last good signal (MQTT online at 100), NOT the LWT-Offline timestamp (500).
        // This assertion FAILS against the old eager-last_seen code.
        self::assertSame(100, (int) $row['last_seen'], 'last_seen must not be advanced by LWT-Offline');

        // http_up=null (false), mqtt_up=0 (false) → both signals down.
        // 500 - 100 = 400 > 180 grace → OFFLINE.
        // This assertion FAILS against the old code because last_seen would be 500,
        // making grace-check 500-500=0 < 180 → stay at previous state (degraded_http).
        self::assertSame(HealthState::OFFLINE, $row['state'], 'state must be OFFLINE after grace expiry');

        // updated_at must be set on every upsert.
        self::assertSame(500, (int) $row['updated_at'], 'updated_at must be set to $now on upsert');
    }

    public function testReportedTopicIsLearnedAndMatchesMqtt(): void
    {
        $sonoff = $this->createMock(Sonoff::class);
        $sonoff->method('getAllStatus')->willReturn($this->statusZero('tasmota_ABC123', '192.168.1.2'));
        $collector = new HealthCollector($this->repo, $sonoff, 180);
        $device = $this->device(1, '');

        $collector->pollHttpDevice($device, 1000);
        $row = $this->repo->get('1');
        self::assertSame('tasmota_ABC123', $row['mqtt_topic']);
        self::assertSame(1, (int) $row['mqtt_expected']);
        self::assertSame(HealthState::DEGRADED_MQTT, $row['state']);

        $collector->handleMqttMessage('tele/tasmota_ABC123/LWT', 'Online', [$device], 1001);
        self::assertSame(HealthState::ONLINE, $this->repo->get('1')['state']);
    }

    public function testDeviceWithMqttDisabledIsOnlineOverHttpAlone(): void
    {
        $sonoff = $this->createMock(Sonoff::class);
        $sonoff->method('getAllStatus')->willReturn($this->statusZero('tasmota_ABC123', null));
        $collector = new HealthCollector($this->repo, $sonoff, 180);

        $collector->pollHttpDevice($this->device(1), 1000);

        $row = $this->repo->get('1');
        self::assertSame(0, (int) $row['mqtt_expected']);
        self::assertSame(HealthState::ONLINE, $row['state']);
    }

    public function testDeviceWithoutAnyTopicIsNotMarkedMqttDegraded(): void
    {
        $sonoff = $this->createMock(Sonoff::class);
        $sonoff->method('getAllStatus')->willReturn(new \stdClass());
        $collector = new HealthCollector($this->repo, $sonoff, 180);

        $collector->pollHttpDevice($this->device(1, ''), 1000);

        self::assertSame(HealthState::ONLINE, $this->repo->get('1')['state']);
    }

    public function testPlaceholderTopicIsNotLearned(): void
    {
        $sonoff = $this->createMock(Sonoff::class);
        $sonoff->method('getAllStatus')->willReturn($this->statusZero('tasmota_%06X', '192.168.1.2'));
        $collector = new HealthCollector($this->repo, $sonoff, 180);

        $collector->pollHttpDevice($this->device(1, ''), 1000);

        self::assertSame('', (string) $this->repo->get('1')['mqtt_topic']);
    }

    public function testRestartedCollectorReusesStoredTopic(): void
    {
        $sonoff = $this->createMock(Sonoff::class);
        $sonoff->method('getAllStatus')->willReturn($this->statusZero('tasmota_ABC123', '192.168.1.2'));
        new HealthCollector($this->repo, $sonoff, 180)->pollHttpDevice($this->device(1, ''), 1000);

        $restarted = new HealthCollector($this->repo, $this->createMock(Sonoff::class), 180);
        $restarted->handleMqttMessage('tele/tasmota_ABC123/LWT', 'Online', [$this->device(1, '')], 1001);

        self::assertSame(1, (int) $this->repo->get('1')['mqtt_up']);
    }

    private function statusZero(string $topic, ?string $mqttHost): \stdClass
    {
        $status = new \stdClass();
        $status->Status = new \stdClass();
        $status->Status->Topic = $topic;
        if (null !== $mqttHost) {
            $status->StatusMQT = new \stdClass();
            $status->StatusMQT->MqttHost = $mqttHost;
        }

        return $status;
    }

    private function device(int $id, string $mqttTopic = 'kitchen'): Device
    {
        return new Device($id, ['dev'.$id], '192.168.1.'.$id, '', '', Device::DEFAULT_IMAGE, 1, true, false, false, [], true, 80, [], false, $mqttTopic);
    }
}
