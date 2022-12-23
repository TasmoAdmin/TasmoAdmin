<?php

namespace Tests\TasmoAdmin\Tasmota;

use TasmoAdmin\Tasmota\ResponseParser;
use PHPUnit\Framework\TestCase;
use Tests\TasmoAdmin\TestUtils;

class ResponseParserTest extends TestCase
{
    public function testProcessResultComaptability(): void
    {
        $parser = new ResponseParser();

        $result = $parser->processResult('{"StatusNET":{"IP": "IP_ADDRESS"}}');

        self::assertEquals("IP_ADDRESS", $result->StatusNET->IPAddress);
    }
}
