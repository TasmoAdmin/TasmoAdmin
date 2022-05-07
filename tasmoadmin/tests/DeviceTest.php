<?php

namespace Tests\TasmoAdmin;

use TasmoAdmin\Device;
use PHPUnit\Framework\TestCase;

class DeviceTest extends TestCase
{
    public function testFromLineEmpty(): void
    {
        self::assertNull(Device::fromLine([]));
    }

    public function testFromLineComplete(): void
    {
        $device = Device::fromLine([0, 'socket-1', '192.168.1.1', 'user', 'pass']);

        self::assertEquals('', $device->position);
        self::assertEquals(['socket-1'], $device->names);
        self::assertEquals('192.168.1.1', $device->ip);
        self::assertEquals('user', $device->username);
        self::assertEquals('pass', $device->password);
    }
}
