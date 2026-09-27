<?php

namespace TasmoAdmin\Health;

use TasmoAdmin\Device;

final class TopicMatcher
{
    public static function isLwtTopic(string $topic): bool
    {
        $parts = explode('/', trim($topic, '/'));
        $last = end($parts);

        return 'LWT' === strtoupper($last);
    }

    public static function isStateTopic(string $topic): bool
    {
        $parts = explode('/', trim($topic, '/'));
        $last = end($parts);

        return 'STATE' === strtoupper($last);
    }

    public static function lwtOnline(string $payload): bool
    {
        return 'ONLINE' === strtoupper(trim($payload));
    }

    /**
     * @param Device[]                  $devices
     * @param array<int|string, string> $fallbackTopics device id => topic reported by the device,
     *                                                  used when no topic is configured in TasmoAdmin
     */
    public static function matchDevice(string $topic, array $devices, array $fallbackTopics = []): ?Device
    {
        // Bound the haystack with slashes so a topic segment matches whole,
        // never as a partial substring (kitchen != kitchenette).
        $haystack = '/'.trim($topic, '/').'/';

        foreach ($devices as $device) {
            $needle = trim('' !== $device->mqttTopic ? $device->mqttTopic : ($fallbackTopics[(string) $device->id] ?? ''), '/');
            if ('' === $needle) {
                continue;
            }

            if (str_contains($haystack, '/'.$needle.'/')) {
                return $device;
            }
        }

        return null;
    }
}
