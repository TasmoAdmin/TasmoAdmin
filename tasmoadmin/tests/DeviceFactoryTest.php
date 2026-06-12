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
        self::assertSame([], $device->friendlyNames);
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
        self::assertEquals(['socket-1'], $device->friendlyNames);
        self::assertFalse($device->deviceConfirmToggle);
        self::assertSame('', $device->mqttTopic);
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
            'friendly-1',
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
        self::assertEquals(['friendly-1'], $device->friendlyNames);
        self::assertFalse($device->deviceConfirmToggle);
        self::assertSame('', $device->mqttTopic);
    }

    public function testFromArrayUsesConfirmToggleColumn(): void
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
            'friendly-1',
            1,
        ]);

        self::assertTrue($device->deviceConfirmToggle);
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
            'device_friendly_name' => ['webui-socket-1'],
            'device_confirm_toggle' => '1',
            'device_mqtt_topic' => 'kitchen-plug',
        ];

        $device = DeviceFactory::fromRequest($request);
        self::assertEquals(1, $device->id);
        self::assertEquals(['socket-1'], $device->names);
        self::assertEquals('socket-1', $device->getName());
        self::assertEquals(['webui-socket-1'], $device->friendlyNames);
        self::assertEquals('192.168.1.1', $device->ip);
        self::assertEquals('user', $device->username);
        self::assertEquals('pass', $device->password);
        self::assertEquals('single', $device->keywords[0]);
        self::assertTrue($device->deviceConfirmToggle);
        self::assertSame('kitchen-plug', $device->mqttTopic);
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
            'device_friendly_name' => ['webui-socket-1', 'webui-socket-2'],
        ];

        $device = DeviceFactory::fromRequest($request);
        self::assertEquals(1, $device->id);
        self::assertEquals(['socket-1', 'socket-2'], $device->names);
        self::assertEquals(['webui-socket-1', 'webui-socket-2'], $device->friendlyNames);
        self::assertEquals('socket-1-socket-2', $device->getName());
        self::assertEquals('192.168.1.1', $device->ip);
        self::assertEquals('user', $device->username);
        self::assertEquals('pass', $device->password);
        self::assertEquals('multi', $device->keywords[0]);
    }

    public function testFromRequestFallsBackToDeviceNamesWhenFriendlyNamesAreMissing(): void
    {
        $request = [
            'device_id' => 1,
            'device_name' => ['socket-1'],
            'device_ip' => '192.168.1.1',
            'device_username' => 'user',
            'device_password' => 'pass',
            'device_friendly_name' => [],
        ];

        $device = DeviceFactory::fromRequest($request);

        self::assertSame(['socket-1'], $device->friendlyNames);
    }

    public function testFromRequestTrimsMqttTopicAndFiltersEmptyNames(): void
    {
        $request = [
            'device_id' => 1,
            'device_name' => ['socket-1', '', 'socket-2'],
            'device_ip' => '192.168.1.1',
            'device_username' => 'user',
            'device_password' => 'pass',
            'device_friendly_name' => ['friendly-1', '', 'friendly-2'],
            'device_mqtt_topic' => '  kitchen-plug  ',
        ];

        $device = DeviceFactory::fromRequest($request);

        self::assertSame(['socket-1', 'socket-2'], $device->names);
        self::assertSame(['friendly-1', 'friendly-2'], $device->friendlyNames);
        self::assertSame('kitchen-plug', $device->mqttTopic);
        self::assertContains('TOPIC#kitchen-plug', $device->keywords);
    }

    public function testFromArrayFiltersEmptyFriendlyNamesAndFallsBackToNames(): void
    {
        $device = DeviceFactory::fromArray([
            0,
            'socket-1|socket-2',
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
            '|',
            0,
            '',
        ]);

        self::assertSame(['socket-1', 'socket-2'], $device->friendlyNames);
        self::assertSame([], array_values(array_filter(
            $device->keywords,
            static fn (string $keyword): bool => str_starts_with($keyword, 'TOPIC#')
        )));
    }

    public function testFromArrayUsesMqttTopicColumn(): void
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
            'friendly-1',
            1,
            'garage-plug',
        ]);

        self::assertSame('garage-plug', $device->mqttTopic);
        self::assertContains('TOPIC#garage-plug', $device->keywords);
    }
}
