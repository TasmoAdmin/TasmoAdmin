<?php

namespace Tests\TasmoAdmin\Health;

use PHPUnit\Framework\TestCase;
use TasmoAdmin\Health\HealthState;

final class HealthStateTest extends TestCase
{
    public function testBothUpIsOnline(): void
    {
        self::assertSame(HealthState::ONLINE, HealthState::resolve(true, true, 100, 100, 180));
    }

    public function testHttpUpMqttDownIsDegradedMqtt(): void
    {
        self::assertSame(HealthState::DEGRADED_MQTT, HealthState::resolve(true, false, 100, 100, 180));
    }

    public function testHttpDownMqttUpIsDegradedHttp(): void
    {
        self::assertSame(HealthState::DEGRADED_HTTP, HealthState::resolve(false, true, 100, 100, 180));
    }

    public function testNoDataIsUnknown(): void
    {
        self::assertSame(HealthState::UNKNOWN, HealthState::resolve(null, null, null, 100, 180));
    }

    public function testBothDownBeyondGraceIsOffline(): void
    {
        // lastSeen=0, now=1000, grace=180 -> 1000 seconds since last contact
        self::assertSame(HealthState::OFFLINE, HealthState::resolve(false, false, 0, 1000, 180, HealthState::ONLINE));
    }

    public function testBothDownWithinGraceKeepsPreviousState(): void
    {
        // lastSeen=900, now=1000, grace=180 -> only 100s elapsed, still in grace
        self::assertSame(HealthState::ONLINE, HealthState::resolve(false, false, 900, 1000, 180, HealthState::ONLINE));
    }

    public function testBothDownWithNoLastSeenIsOffline(): void
    {
        self::assertSame(HealthState::OFFLINE, HealthState::resolve(false, false, null, 1000, 180));
    }
}
