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

    public function testProcessResultv5100(): void
    {
        $parser = new ResponseParser();
        $result = $parser->processResult(TestUtils::loadFixture('response-invalid-v5100.json'));
        self::assertFalse(property_exists($result, 'ERROR'));
    }

    public function testProcessResultNormalizesSingleRelayStates(): void
    {
        $parser = new ResponseParser();
        $result = $parser->processResult('{"POWER":"offen","StatusSTS":{"POWER":"aus"}}');

        self::assertSame('ON', $result->POWER);
        self::assertSame('OFF', $result->StatusSTS->POWER);
    }

    public function testProcessResultNormalizesNestedAndMultiRelayStates(): void
    {
        $parser = new ResponseParser();
        $result = $parser->processResult(
            '{"StatusSTS":{"POWER1":{"STATE":"oben"},"POWER2":"unten"},"POWER1":{"STATE":"an"},"POWER2":"aus"}'
        );

        self::assertSame('ON', $result->StatusSTS->POWER1->STATE);
        self::assertSame('OFF', $result->StatusSTS->POWER2);
        self::assertSame('ON', $result->POWER1);
        self::assertSame('OFF', $result->POWER2);
    }

    public function testProcessResultRemovesControlCharactersBeforeDecoding(): void
    {
        $parser = new ResponseParser();
        $result = $parser->processResult("{\n\"StatusNET\":{\"IP\":\"10.0.0.8\"},\x0B\"POWER\":\"an\"\n}");

        self::assertFalse(property_exists($result, 'ERROR'));
        self::assertSame('10.0.0.8', $result->StatusNET->IPAddress);
        self::assertSame('ON', $result->POWER);
    }

    public function testProcessResultFixesLegacyResultResponses(): void
    {
        $parser = new ResponseParser();
        $result = $parser->processResult("RESULT = {\n  \"POWER\":\"oben\"\n}\n");

        self::assertFalse(property_exists($result, 'ERROR'));
        self::assertSame('ON', $result->POWER);
    }

    public function testProcessResultFixesLegacyErgebnisResponsesWithNanValues(): void
    {
        $parser = new ResponseParser();
        $result = $parser->processResult("ERGEBNIS = {\n  \"Sensor\":{\"Temperature\":nan,\"Humidity\":nan}\n}\n");

        self::assertFalse(property_exists($result, 'ERROR'));
        self::assertSame('NaN', $result->Sensor->Temperature);
        self::assertSame('NaN', $result->Sensor->Humidity);
    }

    public function testProcessResultKeepsUnknownStateTextUntouched(): void
    {
        $parser = new ResponseParser();
        $result = $parser->processResult('{"POWER":"MAYBE","StatusSTS":{"POWER1":"custom"}}');

        self::assertSame('MAYBE', $result->POWER);
        self::assertSame('custom', $result->StatusSTS->POWER1);
    }
}
