<?php

namespace Tests\TasmoAdmin;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use TasmoAdmin\Device;
use TasmoAdmin\DeviceFactory;
use TasmoAdmin\DeviceRepository;
use TasmoAdmin\Sonoff;

class SonoffTest extends TestCase
{
    private vfsStreamDirectory $root;

    protected function setUp(): void
    {
        $this->root = vfsStream::setup('config');
    }

    public function testbuildCmndUrlCredentials(): void
    {
        $device = DeviceFactory::fromArray([0, 'socket-1', '192.168.1.1', 'user', 'pass']);
        $sonoff = new Sonoff($this->getTestDeviceRepository());
        $url = $sonoff->buildCmndUrl($device, Sonoff::COMMAND_INFO_STATUS_ALL);
        self::assertEquals('http://192.168.1.1:80/cm?user=user&password=pass&cmnd=status+0', $url);
    }

    public function testbuildCmndUrlNonStandardPort(): void
    {
        $device = DeviceFactory::fromArray([0, 'socket-1', '192.168.1.1', 'user', 'pass', null, null, null, null, null, null, 5000]);
        $sonoff = new Sonoff($this->getTestDeviceRepository());
        $url = $sonoff->buildCmndUrl($device, Sonoff::COMMAND_INFO_STATUS_ALL);
        self::assertEquals('http://192.168.1.1:5000/cm?user=user&password=pass&cmnd=status+0', $url);
    }

    public function testbuildCmndUrlNoCredentials(): void
    {
        $device = DeviceFactory::fromArray([0, 'socket-1', '192.168.1.1']);
        $sonoff = new Sonoff($this->getTestDeviceRepository());
        $url = $sonoff->buildCmndUrl($device, Sonoff::COMMAND_INFO_STATUS_ALL);
        self::assertEquals('http://192.168.1.1:80/cm?cmnd=status+0', $url);
    }

    public function testGetAllStatusValid(): void
    {
        $device = DeviceFactory::fromArray([0, 'socket-1', '192.168.1.8']);
        $sonoff = new Sonoff($this->getTestDeviceRepository(), $this->getClient([
            new Response(200, [], TestUtils::loadFixture('response-valid.json')),
        ]));
        $result = $sonoff->getAllStatus($device);
        self::assertEquals('socket-1', $result->Status->DeviceName);
    }

    public function testGetAllStatusUnauthorized(): void
    {
        $device = DeviceFactory::fromArray([0, 'socket-1', '192.168.1.8']);
        $sonoff = new Sonoff($this->getTestDeviceRepository(), $this->getClient([
            new Response(401, [], TestUtils::loadFixture('response-unauthorized.json')),
        ]));
        $result = $sonoff->getAllStatus($device);
        self::assertStringContainsString('401 Unauthorized', $result->ERROR);
    }

    public function testSearch(): void
    {
        $sonoff = new Sonoff($this->getTestDeviceRepository(), $this->getClient([
            new Response(200, [], TestUtils::loadFixture('response-valid.json')),
            new Response(401, [], TestUtils::loadFixture('response-unauthorized.json')),
        ]));

        $devices = [];
        foreach (range(1, 2) as $count) {
            $device = DeviceFactory::fromArray([$count, sprintf('socket-%d', $count), sprintf('192.168.1.%d', $count)]);
            $devices[] = $sonoff->buildCmndUrl($device, Sonoff::COMMAND_INFO_STATUS_ALL);
        }

        $result = $sonoff->search($devices);
        self::assertCount(1, $result);
    }

    public function testGetDevicesBasic(): void
    {
        $mockRepository = $this->createMock(DeviceRepository::class);
        $mockRepository->method('getDevices')->willReturn([
            new Device(3, ['socket-3'], '192.168.1.3', 'user', 'pass', 'img', 3, false, false, false, []),
            new Device(2, ['socket-2'], '192.168.1.2', 'user', 'pass', 'img', 2, false, false, false, []),
            new Device(1, ['socket-1'], '192.168.1.1', 'user', 'pass', 'img', 1, false, false, false, []),
        ]);

        $sonoff = new Sonoff($mockRepository);
        $devices = $sonoff->getDevices();
        self::assertEquals(1, $devices[1]->position);
        self::assertEquals(2, $devices[2]->position);
        self::assertEquals(3, $devices[3]->position);
    }

    public function testGetDevicesMissingPosition(): void
    {
        $mockRepository = $this->createMock(DeviceRepository::class);
        $mockRepository->method('getDevices')->willReturn([
            new Device(1, ['socket-1'], '192.168.1.1', 'user', 'pass', 'img', 1, false, false, false, []),
            new Device(2, ['socket-2'], '192.168.1.1', 'user', 'pass', 'img', 0, false, false, false, []),
        ]);

        $sonoff = new Sonoff($mockRepository);
        $devices = $sonoff->getDevices();
        self::assertEquals(1, $devices[1]->position);
        self::assertEquals(['socket-1'], $devices[1]->names);
        self::assertEquals(2, $devices[2]->position);
        self::assertEquals(['socket-2'], $devices[2]->names);
    }

    public function testGetDevicesOverlapPositionBasic(): void
    {
        $mockRepository = $this->createMock(DeviceRepository::class);
        $mockRepository->method('getDevices')->willReturn([
            new Device(1, ['socket-1'], '192.168.1.1', 'user', 'pass', 'img', 1, false, false, false, []),
            new Device(2, ['socket-2'], '192.168.1.1', 'user', 'pass', 'img', 1, false, false, false, []),
            new Device(3, ['socket-3'], '192.168.1.1', 'user', 'pass', 'img', 1, false, false, false, []),
        ]);

        $sonoff = new Sonoff($mockRepository);
        $devices = $sonoff->getDevices();
        self::assertEquals(1, $devices[1]->position);
        self::assertEquals(['socket-1'], $devices[1]->names);
        self::assertEquals(2, $devices[2]->position);
        self::assertEquals(['socket-2'], $devices[2]->names);
        self::assertEquals(3, $devices[3]->position);
        self::assertEquals(['socket-3'], $devices[3]->names);
    }

    public function testGetDevicesOverlapPositionComplex(): void
    {
        $mockRepository = $this->createMock(DeviceRepository::class);
        $mockRepository->method('getDevices')->willReturn([
            new Device(1, ['socket-1'], '192.168.1.1', 'user', 'pass', 'img', 2, false, false, false, []),
            new Device(2, ['socket-2'], '192.168.1.1', 'user', 'pass', 'img', 1, false, false, false, []),
            new Device(3, ['socket-3'], '192.168.1.1', 'user', 'pass', 'img', 2, false, false, false, []),
            new Device(4, ['socket-4'], '192.168.1.1', 'user', 'pass', 'img', 1, false, false, false, []),
        ]);

        $sonoff = new Sonoff($mockRepository);
        $devices = $sonoff->getDevices();
        self::assertEquals(1, $devices[1]->position);
        self::assertEquals(['socket-2'], $devices[1]->names);
        self::assertEquals(2, $devices[2]->position);
        self::assertEquals(['socket-1'], $devices[2]->names);
        self::assertEquals(3, $devices[3]->position);
        self::assertEquals(['socket-3'], $devices[3]->names);
        self::assertEquals(4, $devices[4]->position);
        self::assertEquals(['socket-4'], $devices[4]->names);
    }

    private function getClient(array $responses = []): Client
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);

        return new Client(['handler' => $handlerStack]);
    }

    private function getTestDeviceRepository(): DeviceRepository
    {
        $deviceFile = $this->root->url().'/devices.csv';
        touch($deviceFile);

        $tmpDir = $this->root->url().'/tmp/';
        mkdir($tmpDir);

        return new DeviceRepository($deviceFile, $tmpDir);
    }
}
