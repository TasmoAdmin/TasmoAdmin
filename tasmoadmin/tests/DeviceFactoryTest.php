<?php

namespace Tests\TasmoAdmin;

use PHPUnit\Framework\TestCase;
use TasmoAdmin\DeviceFactory;

class DeviceFactoryTest extends TestCase
{
    public function testFakeDevice(): void
    {
        $device = DeviceFactory::fakeDevice('192.168.1.1', 5000, 'user', 'pass');
        self::assertEquals('192.168.1.1', $device->ip);
        self::assertEquals('user', $device->username);
        self::assertEquals('pass', $device->password);
        self::assertEquals(5000, $device->port);
    }

    public function testFromArrayEmpty(): void
    {
        self::assertNull(DeviceFactory::fromArray([]));
    }

    public function testFromArrayDefaults(): void
    {
        $device = DeviceFactory::fromArray([
            0,
            'socket-1',
            '192.168.1.1',
            'user',
            'pass',
        ]);

        self::assertEquals(0, $device->position);
        self::assertEquals(['socket-1'], $device->names);
        self::assertEquals('192.168.1.1', $device->ip);
        self::assertEquals('user', $device->username);
        self::assertEquals('pass', $device->password);
        self::assertEquals('single', $device->keywords[0]);
        self::assertEquals('bulb_1', $device->img);
        self::assertEquals(80, $device->port);
        self::assertEquals(0, $device->deviceProtectionOn);
        self::assertEquals(0, $device->deviceProtectionOff);
        self::assertEquals(1, $device->deviceAllOff);
        self::assertTrue($device->isUpdatable);
    }

    public function testFromArrayComplete(): void
    {
        $device = DeviceFactory::fromArray([
            0,
            'socket-1',
            '192.168.1.1',
            'user',
            'pass',
            'bulb_2',
            1,
            0,
            1,
            1,
            false,
            5000,
        ]);

        self::assertEquals(1, $device->position);
        self::assertEquals(['socket-1'], $device->names);
        self::assertEquals('192.168.1.1', $device->ip);
        self::assertEquals('user', $device->username);
        self::assertEquals('pass', $device->password);
        self::assertEquals('single', $device->keywords[0]);
        self::assertEquals('bulb_2', $device->img);
        self::assertEquals(1, $device->deviceProtectionOn);
        self::assertEquals(1, $device->deviceProtectionOff);
        self::assertEquals(0, $device->deviceAllOff);
        self::assertFalse($device->isUpdatable);
        self::assertEquals(5000, $device->port);
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
        self::assertEquals('socket-1', $device->getName());
        self::assertEquals('192.168.1.1', $device->ip);
        self::assertEquals('user', $device->username);
        self::assertEquals('pass', $device->password);
        self::assertEquals('single', $device->keywords[0]);
    }

    public function testFromRequestMultipleNames(): void
    {
        $request = [
            'device_id' => 1,
            'device_name' => ['socket-1', 'socket-2'],
            'device_ip' => '192.168.1.1',
            'device_username' => 'user',
            'device_password' => 'pass',
            'device_position' => '',
        ];

        $device = DeviceFactory::fromRequest($request);
        self::assertEquals(1, $device->id);
        self::assertEquals(['socket-1', 'socket-2'], $device->names);
        self::assertEquals('socket-1-socket-2', $device->getName());
        self::assertEquals('192.168.1.1', $device->ip);
        self::assertEquals('user', $device->username);
        self::assertEquals('pass', $device->password);
        self::assertEquals('multi', $device->keywords[0]);
    }
}
