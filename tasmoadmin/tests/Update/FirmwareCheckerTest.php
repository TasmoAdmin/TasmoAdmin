<?php

namespace Tests\TasmoAdmin\Update;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use TasmoAdmin\Update\FirmwareChecker;
use PHPUnit\Framework\TestCase;

class FirmwareCheckerTest extends TestCase
{
    public function testIsValidSuccess(): void
    {
        $firmwareChecker = new FirmwareChecker($this->getClient(new Response(200)));

        self::assertTrue($firmwareChecker->isValid('https://example.org/firmware.bin'));
    }

    public function testIsValidFailure(): void
    {
        $firmwareChecker = new FirmwareChecker($this->getClient(new Response(404)));

        self::assertFalse($firmwareChecker->isValid('https://example.org/firmware.bin'));
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
