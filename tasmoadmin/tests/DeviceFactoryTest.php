<?php

namespace Tests\TasmoAdmin;

use TasmoAdmin\Device;
use PHPUnit\Framework\TestCase;
use TasmoAdmin\DeviceFactory;

class DeviceFactoryTest extends TestCase
{
    public function testFromArrayEmpty(): void
    {
        self::assertNull(DeviceFactory::fromArray([]));
    }

    public function testFromArrayComplete(): void
    {
        $device = DeviceFactory::fromArray([0, 'socket-1', '192.168.1.1', 'user', 'pass']);

        self::assertEquals('', $device->position);
        self::assertEquals(['socket-1'], $device->names);
        self::assertEquals('192.168.1.1', $device->ip);
        self::assertEquals('user', $device->username);
        self::assertEquals('pass', $device->password);
        self::assertEquals('single', $device->keywords[0]);
    }

    public function testFromRequest(): void
    {
        $request = [
            'device_id' => 1,
            'device_name' => ['socket-1'],
            'device_ip' => '192.168.1.1',
            'device_username' => 'user',
            'device_password' => 'pass',
            'device_position' => '',
        ];

        $device = DeviceFactory::fromRequest($request);
        self::assertEquals(1, $device->id);
        self::assertEquals(['socket-1'], $device->names);
        self::assertEquals('192.168.1.1', $device->ip);
        self::assertEquals('user', $device->username);
        self::assertEquals('pass', $device->password);
        self::assertEquals('single', $device->keywords[0]);
    }
}
