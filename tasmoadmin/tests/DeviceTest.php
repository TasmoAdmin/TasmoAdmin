<?php

namespace Tests\TasmoAdmin;

use TasmoAdmin\Device;
use PHPUnit\Framework\TestCase;

class DeviceTest extends TestCase
{
    public function testGetUrlWithAuth(): void
    {
        $device = new Device(1, ['socket-1'], '192.168.1.1', 'user', 'pass', 'img', '1', 0, 0, 0, []);

        $this->assertEquals('http://user:pass@192.168.1.1', $device->getUrlWithAuth());
    }

    public function testGetUrlWithoutAuth(): void
    {
        $device = new Device(1, ['socket-1'], '192.168.1.1', '', '', 'img', '1', 0, 0, 0, []);

        $this->assertEquals('http://192.168.1.1', $device->getUrlWithAuth());
    }
}
