<?php

namespace Tests\TasmoAdmin;

use PHPUnit\Framework\TestCase;
use TasmoAdmin\Device;

class DeviceTest extends TestCase
{
    public function testGetUrlWithAuth(): void
    {
        $device = new Device(1, ['socket-1'], '192.168.1.1', 'user', 'pass', 'img', 1, false, false, false, []);

        $this->assertEquals('http://user:pass@192.168.1.1:80', $device->getUrlWithAuth());
    }

    public function testGetUrlWithoutAuth(): void
    {
        $device = new Device(1, ['socket-1'], '192.168.1.1', '', '', 'img', 1, false, false, false, []);

        $this->assertEquals('http://192.168.1.1:80', $device->getUrlWithAuth());
    }

    public function testGetUrlWithNonStandardPort(): void
    {
        $device = new Device(1, ['socket-1'], '192.168.1.1', '', '', 'img', 1, false, false, false, [], false, 5000);

        $this->assertEquals('http://192.168.1.1:5000', $device->getUrlWithAuth());
    }

    /**
     * @dataProvider backupNameProvider
     */
    public function testGetBackupName(string $expected, array $names): void
    {
        $device = new Device(1, $names, '192.168.1.1', '', '', 'img', 1, false, false, false, []);

        $this->assertEquals($expected, $device->getBackupName());
    }

    public static function backupNameProvider(): array
    {
        return [
            [
                '1-socket-1.dmp',
                ['socket-1'],
            ],
            [
                '1-socket-1-kitchen.dmp',
                ['socket-1', 'kitchen'],
            ],
            [
                '1-socket-1_foo.dmp',
                ['socket-1/foo'],
            ],
        ];
    }
}
