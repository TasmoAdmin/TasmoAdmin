<?php

namespace Tests\TasmoAdmin;

use TasmoAdmin\DeviceFactory;
use TasmoAdmin\Sonoff;
use PHPUnit\Framework\TestCase;

class SonoffTest extends TestCase
{
    public function testbuildCmndUrlCredentials(): void
    {
        $device = DeviceFactory::fromArray([0, 'socket-1', '192.168.1.1', 'user', 'pass']);
        $sonoff = new Sonoff();
        $url = $sonoff->buildCmndUrl($device, 'status 0');
        self::assertEquals('http://192.168.1.1/cm?user=user&password=pass&cmnd=status+0', $url);
    }

    public function testbuildCmndUrlNoCredentials(): void
    {
        $device = DeviceFactory::fromArray([0, 'socket-1', '192.168.1.1']);
        $sonoff = new Sonoff();
        $url = $sonoff->buildCmndUrl($device, 'status 0');
        self::assertEquals('http://192.168.1.1/cm?cmnd=status+0', $url);
    }
}
