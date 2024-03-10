<?php

namespace Tests\TasmoAdmin\Tasmota;

use PHPUnit\Framework\TestCase;
use TasmoAdmin\Tasmota\ResponseParser;
use Tests\TasmoAdmin\TestUtils;

class ResponseParserTest extends TestCase
{
    public function testProcessResultCompatability(): void
    {
        $parser = new ResponseParser();

        $result = $parser->processResult('{"StatusNET":{"IP": "IP_ADDRESS"}}');

        self::assertEquals('IP_ADDRESS', $result->StatusNET->IPAddress);
    }

    public function testProcessResultInvalid(): void
    {
        $parser = new ResponseParser();
        $result = $parser->processResult('{"Invalid": "\xfc\xa1\xa1\xa1\xa1\xa1"}');

        self::assertTrue(property_exists($result, 'ERROR'));
        self::assertStringContainsString('{"Invalid": "\xfc\xa1\xa1\xa1\xa1\xa1"}', $result->ERROR);
    }

    public function testProcessResultv8500(): void
    {
        $parser = new ResponseParser();
        $result = $parser->processResult(TestUtils::loadFixture('response-invalid-v8500.json'));
        self::assertFalse(property_exists($result, 'ERROR'));
    }
}
