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
}
