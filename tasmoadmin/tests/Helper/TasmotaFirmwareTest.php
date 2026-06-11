<?php

namespace Tests\TasmoAdmin\Helper;

use PHPUnit\Framework\TestCase;
use TasmoAdmin\Helper\TasmotaFirmware;

class TasmotaFirmwareTest extends TestCase
{
    public function testGettersReturnConfiguredValues(): void
    {
        $firmware = new TasmotaFirmware('tasmota.bin', 'https://example.com/tasmota.bin');

        self::assertSame('tasmota.bin', $firmware->getName());
        self::assertSame('https://example.com/tasmota.bin', $firmware->getUrl());
    }
}
