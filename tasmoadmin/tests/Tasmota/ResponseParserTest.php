<?php

namespace Tests\TasmoAdmin\Tasmota;

use TasmoAdmin\Tasmota\ResponseParser;
use PHPUnit\Framework\TestCase;
use Tests\TasmoAdmin\TestUtils;

class ResponseParserTest extends TestCase
{
    public function testProcessResultCompatability(): void
    {
        $parser = new ResponseParser();

        $result = $parser->processResult('{"StatusNET":{"IP": "IP_ADDRESS"}}');

        self::assertEquals("IP_ADDRESS", $result->StatusNET->IPAddress);
    }

    public function testProcessResultInvalid(): void
    {
        $parser = new ResponseParser();
        $result = $parser->processResult('{"Invalid": "\xfc\xa1\xa1\xa1\xa1\xa1"}');

        self::assertTrue(property_exists($result, 'ERROR'));
        self::assertStringContainsString('{"Invalid": "\xfc\xa1\xa1\xa1\xa1\xa1"}', $result->ERROR);
    }
}
