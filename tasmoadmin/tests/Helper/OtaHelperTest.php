<?php

namespace Tests\TasmoAdmin\Helper;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TasmoAdmin\Config;
use TasmoAdmin\Helper\OtaHelper;

class OtaHelperTest extends TestCase
{
    private vfsStreamDirectory $root;

    private array $serverBackup = [];

    protected function setUp(): void
    {
        $this->root = vfsStream::setup('config');
        $this->serverBackup = $_SERVER;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->serverBackup;
    }

    public function testGetFirmwareUrl(): void
    {
        $config = new Config($this->root->url().'/', $this->root->url().'/');
        $config->write('ota_server_ip', '192.168.1.1');
        $config->write('ota_server_port', '8080');

        $otaHelper = new OtaHelper($config, '/_BASEURL_/');
        self::assertEquals('http://192.168.1.1:8080/_BASEURL_/data/firmwares/my-firmware.bin', $otaHelper->getFirmwareUrl('path/to/my-firmware.bin'));
    }

    public function testGetFirmwareUrlUsesServerDefaultsWhenOtaConfigUnset(): void
    {
        $_SERVER['SERVER_ADDR'] = '10.0.0.5';
        $_SERVER['SERVER_PORT'] = '9999';

        $otaHelper = new OtaHelper(
            new Config($this->root->url().'/', $this->root->url().'/'),
            '/_BASEURL_/'
        );

        self::assertSame(
            'http://10.0.0.5:9999/_BASEURL_/data/firmwares/default.bin',
            $otaHelper->getFirmwareUrl('default.bin')
        );
    }

    #[DataProvider('firmwarePathProvider')]
    public function testGetFirmwareUrlUsesBasenameForPathVariants(string $firmware, string $expectedFileName): void
    {
        $config = new Config($this->root->url().'/', $this->root->url().'/');
        $config->write('ota_server_ip', '192.168.1.1');
        $config->write('ota_server_port', '8080');

        $otaHelper = new OtaHelper($config, '/_BASEURL_/');

        self::assertSame(
            sprintf('http://192.168.1.1:8080/_BASEURL_/data/firmwares/%s', $expectedFileName),
            $otaHelper->getFirmwareUrl($firmware)
        );
    }

    public static function firmwarePathProvider(): array
    {
        return [
            ['my-firmware.bin', 'my-firmware.bin'],
            ['path/to/my-firmware.bin', 'my-firmware.bin'],
            ['../nested/path/my-firmware.bin', 'my-firmware.bin'],
        ];
    }
}
