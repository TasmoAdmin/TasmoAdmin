<?php

namespace Tests\TasmoAdmin;

use PHPUnit\Framework\Attributes\DataProvider;
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

    public function testGetUrlWithoutAuthWhenCredentialsArePartial(): void
    {
        $userOnly = new Device(1, ['socket-1'], '192.168.1.1', 'user', '', 'img', 1, false, false, false, []);
        $passwordOnly = new Device(1, ['socket-1'], '192.168.1.1', '', 'pass', 'img', 1, false, false, false, []);

        $this->assertEquals('http://192.168.1.1:80', $userOnly->getUrlWithAuth());
        $this->assertEquals('http://192.168.1.1:80', $passwordOnly->getUrlWithAuth());
    }

    public function testGetUrlWithNonStandardPort(): void
    {
        $device = new Device(1, ['socket-1'], '192.168.1.1', '', '', 'img', 1, false, false, false, [], false, 5000);

        $this->assertEquals('http://192.168.1.1:5000', $device->getUrlWithAuth());
    }

    #[DataProvider('nameProvider')]
    public function testGetName(string $expected, array $names): void
    {
        $device = new Device(1, $names, '192.168.1.1', '', '', 'img', 1, false, false, false, []);

        $this->assertEquals($expected, $device->getName());
    }

    public static function nameProvider(): array
    {
        return [
            [
                'socket-1',
                ['socket-1'],
            ],
            [
                'socket-1-socket-1',
                ['socket-1', 'socket-1'],
            ],
            [
                'socket-1-kitchen',
                ['socket-1', 'kitchen'],
            ],
            [
                'socket-1_foo',
                ['socket-1_foo'],
            ],
        ];
    }

    #[DataProvider('backupNameProvider')]
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
                '1-socket-1-socket-1.dmp',
                ['socket-1', 'socket-1'],
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

    public function testGetFriendlyNamesFallsBackToAdminNames(): void
    {
        $device = new Device(1, ['socket-1'], '192.168.1.1', '', '', 'img', 1, false, false, false, []);

        $this->assertSame(['socket-1'], $device->getFriendlyNames());
    }

    public function testGetFriendlyNamesUsesStoredFriendlyNames(): void
    {
        $device = new Device(
            1,
            ['office-lamp'],
            '192.168.1.1',
            '',
            '',
            'img',
            1,
            false,
            false,
            false,
            [],
            true,
            80,
            ['lamp-webui']
        );

        $this->assertSame(['lamp-webui'], $device->getFriendlyNames());
    }
}
