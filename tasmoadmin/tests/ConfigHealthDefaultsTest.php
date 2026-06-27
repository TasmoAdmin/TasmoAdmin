<?php

namespace Tests\TasmoAdmin;

use PHPUnit\Framework\TestCase;
use TasmoAdmin\Config;

final class ConfigHealthDefaultsTest extends TestCase
{
    private string $dataDir;

    protected function setUp(): void
    {
        $this->dataDir = sys_get_temp_dir().'/ta-cfg-'.bin2hex(random_bytes(4)).'/';
        mkdir($this->dataDir, 0777, true);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->dataDir.'*') ?: []);
        @rmdir($this->dataDir);
    }

    public function testHealthDefaultsArePresent(): void
    {
        $config = new Config($this->dataDir, $this->dataDir);

        self::assertSame('1', $config->read('health_enabled'));
        self::assertSame('60', $config->read('health_http_poll_interval'));
        self::assertSame('180', $config->read('health_offline_grace'));
        self::assertSame('#', $config->read('health_mqtt_subscription'));
    }
}
