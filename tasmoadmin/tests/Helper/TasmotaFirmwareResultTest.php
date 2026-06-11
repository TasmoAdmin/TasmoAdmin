<?php

namespace Tests\TasmoAdmin\Helper;

use PHPUnit\Framework\TestCase;
use TasmoAdmin\Helper\TasmotaFirmware;
use TasmoAdmin\Helper\TasmotaFirmwareResult;

class TasmotaFirmwareResultTest extends TestCase
{
    public function testGettersReturnVersionDateAndFirmwares(): void
    {
        $publishedAt = new \DateTime('2026-06-11T00:00:00+00:00');
        $firmwares = [new TasmotaFirmware('tasmota.bin', 'https://example.com/tasmota.bin')];
        $result = new TasmotaFirmwareResult('v5.1.0', $publishedAt, $firmwares);

        self::assertSame('v5.1.0', $result->getVersion());
        self::assertSame($publishedAt, $result->getPublishDate());
        self::assertSame($firmwares, $result->getFirmwares());
    }
}
