<?php

namespace Tests\TasmoAdmin\Helper;

use org\bovigo\vfs\vfsStream;
use TasmoAdmin\Config;
use TasmoAdmin\Helper\OtaHelper;
use PHPUnit\Framework\TestCase;

class OtaHelperTest extends TestCase
{
    protected function setUp(): void
    {
        $this->root = vfsStream::setup('config');
    }

    public function testGetFirmwareUrl(): void
    {
        $config = new Config($this->root->url() . '/');
        $config->write('ota_server_ip', '192.168.1.1');
        $config->write('ota_server_port', '8080');

        $otaHelper = new OtaHelper($config, '/_BASEURL_/');
        self::assertEquals('http://192.168.1.1:8080/_BASEURL_/data/firmwares/my-firmware.bin', $otaHelper->getFirmwareUrl('my-firmware.bin'));
    }
}
