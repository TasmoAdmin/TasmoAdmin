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

    public function testGetDevices(): void
    {
        $devices = $this->getValidRepo()->getDevices();
        self::assertCount(3, $devices);
    }

    public function testGetDevicesEmptyRepo(): void
    {
        $devices = $this->getEmptyRepo()->getDevices();
        self::assertCount(0, $devices);
    }

    public function testSetDeviceValueMissingDevice(): void
    {
        $repo = $this->getEmptyRepo();
        self::assertNull($repo->setDeviceValue('1', 'names', '1'));
    }

    public function testSetDeviceValueInvalidField(): void
    {
        $repo = $this->getValidRepo();
        self::assertNull($repo->setDeviceValue('1', 'random', '1'));
    }

    private function getValidRepo(): DeviceRepository
    {
        return new DeviceRepository(TestUtils::getFixturePath('devices.csv'));
    }

    private function getEmptyRepo(): DeviceRepository
    {
       return new DeviceRepository(TestUtils::getFixturePath('empty_devices.csv'));
    }
}
