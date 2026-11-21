<?php

namespace Tests\TasmoAdmin\Health;

use PHPUnit\Framework\TestCase;
use TasmoAdmin\Health\HealthState;
use TasmoAdmin\Health\HealthView;

final class HealthViewTest extends TestCase
{
    public function testLabelKeyMapsKnownStates(): void
    {
        self::assertSame('STATE_ONLINE', HealthView::labelKey(HealthState::ONLINE));
        self::assertSame('STATE_DEGRADED_MQTT', HealthView::labelKey(HealthState::DEGRADED_MQTT));
        self::assertSame('STATE_DEGRADED_HTTP', HealthView::labelKey(HealthState::DEGRADED_HTTP));
        self::assertSame('STATE_OFFLINE', HealthView::labelKey(HealthState::OFFLINE));
        self::assertSame('STATE_UNKNOWN', HealthView::labelKey('other'));
    }

    public function testRelativeTimeBuckets(): void
    {
        self::assertSame('LAST_SEEN_NEVER', HealthView::relativeTime(null, 1000));
        self::assertSame('LAST_SEEN_SECONDS', HealthView::relativeTime(970, 1000));
        self::assertSame('LAST_SEEN_MINUTES', HealthView::relativeTime(700, 1000));
        self::assertSame('LAST_SEEN_HOURS', HealthView::relativeTime(1000, 8200));
        self::assertSame('LAST_SEEN_DAYS', HealthView::relativeTime(1000, 180000));
    }

    public function testRelativeTimeValueWorksWithVsprintfPlaceholders(): void
    {
        self::assertSame('12s ago', vsprintf('%ds ago', [HealthView::relativeTimeValue(988, 1000)]));
        self::assertSame('5m ago', vsprintf('%dm ago', [HealthView::relativeTimeValue(700, 1000)]));
    }

    public function testChannelSummaryKeysDescribeActualChannelState(): void
    {
        self::assertSame('CHANNEL_BOTH_UP', HealthView::channelSummaryKey(true, true));
        self::assertSame('CHANNEL_HTTP_ONLY', HealthView::channelSummaryKey(true, false));
        self::assertSame('CHANNEL_MQTT_ONLY', HealthView::channelSummaryKey(false, true));
        self::assertSame('CHANNEL_BOTH_DOWN', HealthView::channelSummaryKey(false, false));
    }

    public function testChannelSummaryForHttpOnlyDevicesIsNeutral(): void
    {
        self::assertSame('CHANNEL_HTTP_MONITORED', HealthView::channelSummaryKey(true, false, false));
        self::assertSame('bg-secondary', HealthView::channelSummaryClass(true, false, false));
        self::assertSame('bg-danger', HealthView::channelSummaryClass(false, false, false));
        self::assertSame('bg-warning text-dark', HealthView::channelSummaryClass(true, false));
    }

    public function testSeverityRanksWorstStatesFirst(): void
    {
        self::assertLessThan(HealthView::severityRank(HealthState::DEGRADED_MQTT), HealthView::severityRank(HealthState::OFFLINE));
        self::assertLessThan(HealthView::severityRank(HealthState::UNKNOWN), HealthView::severityRank(HealthState::DEGRADED_HTTP));
        self::assertLessThan(HealthView::severityRank(HealthState::ONLINE), HealthView::severityRank(HealthState::UNKNOWN));
    }

    public function testSignalClassMapsRssiStrength(): void
    {
        self::assertSame('signal-bars-unknown', HealthView::signalClass(null));
        self::assertSame('signal-bars-weak', HealthView::signalClass(25));
        self::assertSame('signal-bars-medium', HealthView::signalClass(62));
        self::assertSame('signal-bars-strong', HealthView::signalClass(88));
    }
}
