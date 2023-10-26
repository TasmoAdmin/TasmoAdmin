<?php

namespace Tests\TasmoAdmin;

use TasmoAdmin\Device;
use PHPUnit\Framework\TestCase;

class DeviceTest extends TestCase
{
    public function testGetUrlWithAuth(): void
    {
        $device = new Device(1, ['socket-1'], '192.168.1.1', 'user', 'pass', 'img', 1, 0, 0, 0, []);

        $this->assertEquals('http://user:pass@192.168.1.1', $device->getUrlWithAuth());
    }

    public function testGetUrlWithoutAuth(): void
    {
        $device = new Device(1, ['socket-1'], '192.168.1.1', '', '', 'img', 1, 0, 0, 0, []);

        $this->assertEquals('http://192.168.1.1', $device->getUrlWithAuth());
    }

    /**
     * @dataProvider backupNameProvider
     * @param string $expected
     * @param array $names
     * @return void
     */
    public function testGetBackupName(string $expected, array $names): void
    {
        $device = new Device(1, $names, '192.168.1.1', '', '', 'img', 1, 0, 0, 0, []);

        $this->assertEquals($expected, $device->getBackupName());
    }

    public static function backupNameProvider(): array
    {
        return [
            [
                '1-socket-1.dmp',
                ['socket-1']
            ],
            [
                '1-socket-1-kitchen.dmp',
                ['socket-1', 'kitchen']
            ],
            [
                '1-socket-1_foo.dmp',
                ['socket-1/foo']
            ],
        ];
    }
}
