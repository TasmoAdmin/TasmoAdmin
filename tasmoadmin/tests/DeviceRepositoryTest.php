<?php

namespace Tests\TasmoAdmin;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use TasmoAdmin\DeviceRepository;
use PHPUnit\Framework\TestCase;

class DeviceRepositoryTest extends TestCase
{
    private vfsStreamDirectory $root;

    protected function setUp(): void
    {
        $this->root = vfsStream::setup('config');
    }

    public function testSaveDevicesEmptyDevices(): void
    {
        $repo = $this->getVirtualRepo();
        $devices = [];
        $repo->saveDevices($devices, 'user', 'pass');
        self::assertCount(0, $repo->getDevices());
    }

    public function testSaveDevicesDevices(): void
    {
        $repo = $this->getVirtualRepo();
        $devices = [
            [
                'device_name' => ['socket-1'],
                'device_ip' => '127.0.0.1',
                'device_img' => 'orange',
                'device_position' => 1,
            ]
        ];
        $repo->saveDevices($devices, 'user', 'pass');
        self::assertCount(1, $repo->getDevices());
        $device = $repo->getDevices()[0];
        self::assertEquals(['socket-1'], $device->names);

    }

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

    public function testGetDeviceByIdInvalidId(): void
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

    public function testSetDeviceValueValid(): void
    {
        $repo = $this->getVirtualRepo();
        $devices = [
            [
                'device_name' => ['socket-1']
            ]
        ];
        $repo->saveDevices($devices, 'user', 'pass');
        $repo->setDeviceValue('1', 'names', ['socket-2']);
        $device = $repo->getDeviceById('1');
        self::assertEquals(['socket-2'], $device->names);
    }

    public function testRemoveDeviceValid(): void
    {
        $repo = $this->getVirtualRepoWithDevices(5);
        $repo->removeDevice('1');
        self::assertNull($repo->getDeviceById('1'));
        self::assertCount(4, $repo->getDevices());
    }

    public function testRemoveDeviceInvalid(): void
    {
        $repo = $this->getVirtualRepoWithDevices(5);
        $repo->removeDevice('6');
        self::assertCount(5, $repo->getDevices());
    }

    private function getVirtualRepoWithDevices(int $count): DeviceRepository
    {
        $repo = $this->getVirtualRepo();
        $devices = [];
        for ($i = 1; $i <= $count; $i++) {
            $devices[] = [
                'device_name' => [sprintf('socket-%d', $i)],
                'device_ip' => sprintf('127.0.0.%d', $i),
                'device_img' => 'orange',
                'device_position' => $i,
            ];
        }

        $repo->saveDevices($devices, 'user', 'pass');

        return $repo;
    }

    private function getVirtualRepo(): DeviceRepository
    {
        $deviceFile = $this->root->url() . '/devices.csv';
        touch($deviceFile);

        $tmpDir = $this->root->url() . '/tmp/';
        mkdir($tmpDir);
        return new DeviceRepository($deviceFile, $tmpDir);
    }

    private function getValidRepo(): DeviceRepository
    {
        $tmpDir = $this->root->url() . '/tmp/';
        mkdir($tmpDir);
        return new DeviceRepository(TestUtils::getFixturePath('devices.csv'), $tmpDir);
    }

    private function getEmptyRepo(): DeviceRepository
    {
        $tmpDir = $this->root->url() . '/tmp/';
        mkdir($tmpDir);
        return new DeviceRepository(TestUtils::getFixturePath('empty_devices.csv'), $tmpDir);
    }
}
