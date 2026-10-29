<?php

namespace Tests\TasmoAdmin\Health;

use PHPUnit\Framework\TestCase;
use TasmoAdmin\Health\HealthRepository;

final class HealthRepositoryTest extends TestCase
{
    private string $dir;
    private string $dbPath;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir().'/ta-health-'.bin2hex(random_bytes(4));
        mkdir($this->dir, 0o777, true);
        $this->dbPath = $this->dir.'/health.db';
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->dir.'/*') ?: []);
        @rmdir($this->dir);
    }

    public function testReadsDegradeGracefullyWhenDbMissing(): void
    {
        $repo = new HealthRepository($this->dbPath);
        self::assertSame([], $repo->all());
        self::assertNull($repo->get('1'));
    }

    public function testUpsertThenGet(): void
    {
        $repo = new HealthRepository($this->dbPath);
        $repo->ensureSchema();
        $repo->upsert([
            'device_id' => '1',
            'state' => 'online',
            'http_up' => 1,
            'mqtt_up' => 1,
            'last_http_ok' => 1000,
            'last_mqtt_ok' => 1000,
            'last_seen' => 1000,
            'rssi' => -55,
            'signal' => 80,
            'updated_at' => 1000,
        ]);

        $row = $repo->get('1');
        self::assertNotNull($row);
        self::assertSame('online', $row['state']);
        self::assertSame(-55, (int) $row['rssi']);
    }

    public function testUpsertUpdatesExistingRow(): void
    {
        $repo = new HealthRepository($this->dbPath);
        $repo->ensureSchema();
        $repo->upsert(['device_id' => '1', 'state' => 'online', 'updated_at' => 1000]);
        $repo->upsert(['device_id' => '1', 'state' => 'offline', 'updated_at' => 2000]);

        self::assertCount(1, $repo->all());
        self::assertSame('offline', $repo->get('1')['state']);
    }

    public function testEnsureSchemaIsIdempotent(): void
    {
        $repo = new HealthRepository($this->dbPath);
        $repo->ensureSchema();
        $repo->ensureSchema();
        self::assertSame([], $repo->all());
    }

    public function testEnsureSchemaAddsColumnsToExistingDatabase(): void
    {
        $pdo = new \PDO('sqlite:'.$this->dbPath);
        $pdo->exec('CREATE TABLE device_health (device_id TEXT PRIMARY KEY, state TEXT, http_up INTEGER, mqtt_up INTEGER, last_http_ok INTEGER, last_mqtt_ok INTEGER, last_seen INTEGER, rssi INTEGER, signal INTEGER, updated_at INTEGER)');
        $pdo->exec("INSERT INTO device_health (device_id, state) VALUES ('1', 'online')");
        unset($pdo);

        $repo = new HealthRepository($this->dbPath);
        $repo->ensureSchema();
        $repo->ensureSchema();
        $repo->upsert(['device_id' => '1', 'state' => 'online', 'mqtt_topic' => 'kitchen', 'mqtt_expected' => 1]);

        $row = $repo->get('1');
        self::assertSame('kitchen', $row['mqtt_topic']);
        self::assertSame(1, (int) $row['mqtt_expected']);
    }
}
