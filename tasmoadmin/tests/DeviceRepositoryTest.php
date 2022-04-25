<?php

namespace Tests\TasmoAdmin;

use TasmoAdmin\DeviceRepository;
use PHPUnit\Framework\TestCase;

class DeviceRepositoryTest extends TestCase
{
    public function testGetDeviceByIdValid(): void
    {
        $repo = $this->getValidRepo();
        $device = $repo->getDeviceById('1');

        self::assertEquals(1, $device->id);
        self::assertEquals(['socket-1'], $device->names);
        self::assertEquals('192.168.1.2', $device->ip);
        self::assertEquals('user', $device->username);
        self::assertEquals('password', $device->password);
        self::assertEquals('bulb_1', $device->img);
        self::assertEquals(2, $device->position);
    }

    public function testGetDeviceByIdInvalid(): void
    {
        $repo = $this->getValidRepo();
        self::assertNull($repo->getDeviceById('9'));
    }

    private function getValidRepo(): DeviceRepository
    {
        return new DeviceRepository(TestUtils::getFixturePath('devices.csv'));
    }
}
