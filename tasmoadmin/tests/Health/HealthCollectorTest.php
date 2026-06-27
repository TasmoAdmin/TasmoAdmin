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
        mkdir($this->dir, 0777, true);
        $this->repo = new HealthRepository($this->dir.'/health.db');
        $this->repo->ensureSchema();
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->dir.'/*') ?: []);
        @rmdir($this->dir);
    }

    private function device(int $id, string $mqttTopic = 'kitchen'): Device
    {
        return new Device($id, ['dev'.$id], '192.168.1.'.$id, '', '', Device::DEFAULT_IMAGE, 1, true, false, false, [], true, 80, [], false, $mqttTopic);
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

    public function testNonLwtMessageIsIgnored(): void
    {
        $sonoff = $this->createMock(Sonoff::class);
        $collector = new HealthCollector($this->repo, $sonoff, 180);
        $collector->handleMqttMessage('tele/kitchen/STATE', '{"Wifi":{}}', [$this->device(1)], 1000);

        self::assertNull($this->repo->get('1'));
    }
}
