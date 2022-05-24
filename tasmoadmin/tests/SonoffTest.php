<?php

namespace Tests\TasmoAdmin;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
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

    public function testGetAllStatusValid(): void
    {
        $device = DeviceFactory::fromArray([0, 'socket-1', '192.168.1.8']);
        $sonoff = new Sonoff($this->getClient(
            new Response(200, [], TestUtils::loadFixture('response-valid.json'))
        ));
        $result = $sonoff->getAllStatus($device);
        self::assertEquals('socket-1', $result->Status->DeviceName);
    }

    public function testGetAllStatusUnauthorized(): void
    {
        $device = DeviceFactory::fromArray([0, 'socket-1', '192.168.1.8']);
        $sonoff = new Sonoff($this->getClient(
            new Response(401, [], TestUtils::loadFixture('response-unauthorized.json'))
        ));
        $result = $sonoff->getAllStatus($device);
        self::assertStringContainsString('401 Unauthorized', $result->ERROR);
    }

    private function getClient(?Response $response = null): Client
    {
        $responses = [];
        if ($response) {
            $responses[] = $response;
        }
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        return new Client(['handler' => $handlerStack]);
    }
}
