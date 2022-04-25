<?php

namespace Tests\TasmoAdmin;

use TasmoAdmin\Device;
use PHPUnit\Framework\TestCase;

class DeviceTest extends TestCase
{
    public function testFromLineEmpty(): void
    {
        self::assertNull(Device::fromLine([]));
    }

    public function testFromLineWithId(): void
    {
        self::assertNull(Device::fromLine([0 => '1']));
    }
}
