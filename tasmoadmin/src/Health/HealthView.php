<?php

namespace TasmoAdmin\Health;

final class HealthView
{
    public const STATES = [
        HealthState::ONLINE,
        HealthState::DEGRADED_MQTT,
        HealthState::DEGRADED_HTTP,
        HealthState::OFFLINE,
        HealthState::UNKNOWN,
    ];

    public static function badgeClass(string $state): string
    {
        return match ($state) {
            HealthState::ONLINE => 'bg-success',
            HealthState::DEGRADED_MQTT, HealthState::DEGRADED_HTTP => 'bg-warning text-dark',
            HealthState::OFFLINE => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public static function dotClass(string $state): string
    {
        return match ($state) {
            HealthState::ONLINE => 'health-dot-online',
            HealthState::DEGRADED_MQTT, HealthState::DEGRADED_HTTP => 'health-dot-degraded',
            HealthState::OFFLINE => 'health-dot-offline',
            default => 'health-dot-unknown',
        };
    }

    public static function labelKey(string $state): string
    {
        return match ($state) {
            HealthState::ONLINE => 'STATE_ONLINE',
            HealthState::DEGRADED_MQTT => 'STATE_DEGRADED_MQTT',
            HealthState::DEGRADED_HTTP => 'STATE_DEGRADED_HTTP',
            HealthState::OFFLINE => 'STATE_OFFLINE',
            default => 'STATE_UNKNOWN',
        };
    }

    public static function severityRank(string $state): int
    {
        return match ($state) {
            HealthState::OFFLINE => 0,
            HealthState::DEGRADED_MQTT, HealthState::DEGRADED_HTTP => 1,
            HealthState::UNKNOWN => 2,
            HealthState::ONLINE => 3,
            default => 4,
        };
    }

    public static function channelSummaryKey(bool $httpUp, bool $mqttUp, bool $mqttExpected = true): string
    {
        if (!$mqttExpected) {
            return 'CHANNEL_HTTP_MONITORED';
        }
        if ($httpUp && $mqttUp) {
            return 'CHANNEL_BOTH_UP';
        }
        if ($httpUp) {
            return 'CHANNEL_HTTP_ONLY';
        }
        if ($mqttUp) {
            return 'CHANNEL_MQTT_ONLY';
        }

        return 'CHANNEL_BOTH_DOWN';
    }

    public static function channelSummaryClass(bool $httpUp, bool $mqttUp, bool $mqttExpected = true): string
    {
        if (!$mqttExpected) {
            return $httpUp ? 'bg-secondary' : 'bg-danger';
        }
        if ($httpUp && $mqttUp) {
            return 'bg-success';
        }
        if ($httpUp || $mqttUp) {
            return 'bg-warning text-dark';
        }

        return 'bg-danger';
    }

    public static function signalClass(?int $rssi): string
    {
        if (null === $rssi) {
            return 'signal-bars-unknown';
        }

        if ($rssi >= 75) {
            return 'signal-bars-strong';
        }

        if ($rssi >= 50) {
            return 'signal-bars-medium';
        }

        return 'signal-bars-weak';
    }

    public static function relativeTime(?int $timestamp, int $now): string
    {
        if (null === $timestamp || $timestamp <= 0) {
            return 'LAST_SEEN_NEVER';
        }

        $seconds = max(0, $now - $timestamp);
        if ($seconds < 60) {
            return 'LAST_SEEN_SECONDS';
        }
        if ($seconds < 3600) {
            return 'LAST_SEEN_MINUTES';
        }
        if ($seconds < 86400) {
            return 'LAST_SEEN_HOURS';
        }

        return 'LAST_SEEN_DAYS';
    }

    public static function relativeTimeValue(?int $timestamp, int $now): int
    {
        if (null === $timestamp || $timestamp <= 0) {
            return 0;
        }

        $seconds = max(0, $now - $timestamp);
        if ($seconds < 60) {
            return $seconds;
        }
        if ($seconds < 3600) {
            return (int) floor($seconds / 60);
        }
        if ($seconds < 86400) {
            return (int) floor($seconds / 3600);
        }

        return (int) floor($seconds / 86400);
    }
}
