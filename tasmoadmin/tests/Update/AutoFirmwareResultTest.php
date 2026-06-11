<?php

namespace Tests\TasmoAdmin\Update;

use PHPUnit\Framework\TestCase;
use TasmoAdmin\Update\AutoFirmwareResult;

class AutoFirmwareResultTest extends TestCase
{
    public function testExposesAllConfiguredValues(): void
    {
        $publishedAt = new \DateTime('2026-06-11T00:00:00+00:00');
        $result = new AutoFirmwareResult(
            'https://example.com/full.bin',
            'https://example.com/minimal.bin',
            'v5.1.0',
            $publishedAt
        );

        self::assertSame('https://example.com/full.bin', $result->getFirmwareUrl());
        self::assertTrue($result->hasMinimalFirmware());
        self::assertSame('https://example.com/minimal.bin', $result->getMinimalFirmwareUrl());
        self::assertSame('v5.1.0', $result->getTagName());
        self::assertSame($publishedAt, $result->getPublishedAt());
    }

    public function testReportsMissingMinimalFirmware(): void
    {
        $result = new AutoFirmwareResult(
            'https://example.com/full.bin',
            null,
            'v5.1.0',
            new \DateTime('2026-06-11T00:00:00+00:00')
        );

        self::assertFalse($result->hasMinimalFirmware());
        self::assertNull($result->getMinimalFirmwareUrl());
    }
}
