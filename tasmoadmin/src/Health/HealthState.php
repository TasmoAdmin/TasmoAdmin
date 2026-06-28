<?php

namespace TasmoAdmin\Health;

final class HealthState
{
    public const ONLINE = 'online';
    public const DEGRADED_MQTT = 'degraded_mqtt';
    public const DEGRADED_HTTP = 'degraded_http';
    public const OFFLINE = 'offline';
    public const UNKNOWN = 'unknown';

    public static function resolve(
        ?bool $httpUp,
        ?bool $mqttUp,
        ?int $lastSeen,
        int $now,
        int $graceSeconds,
        string $previousState = self::UNKNOWN,
    ): string {
        if (null === $httpUp && null === $mqttUp) {
            return self::UNKNOWN;
        }

        $http = true === $httpUp;
        $mqtt = true === $mqttUp;

        if ($http) {
            return $mqtt ? self::ONLINE : self::DEGRADED_MQTT;
        }
        if ($mqtt) {
            return self::DEGRADED_HTTP;
        }

        // Both currently down.
        if (null !== $lastSeen && ($now - $lastSeen) < $graceSeconds) {
            return $previousState;
        }

        return self::OFFLINE;
    }
}
