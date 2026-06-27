<?php

namespace TasmoAdmin\Health;

use TasmoAdmin\Device;

final class TopicMatcher
{
    public static function isLwtTopic(string $topic): bool
    {
        $parts = explode('/', trim($topic, '/'));
        $last = end($parts);

        return is_string($last) && 'LWT' === strtoupper($last);
    }

    public static function lwtOnline(string $payload): bool
    {
        return 'ONLINE' === strtoupper(trim($payload));
    }

    /**
     * @param Device[] $devices
     */
    public static function matchDevice(string $topic, array $devices): ?Device
    {
        // Bound the haystack with slashes so a topic segment matches whole,
        // never as a partial substring (kitchen != kitchenette).
        $haystack = '/'.trim($topic, '/').'/';

        foreach ($devices as $device) {
            $needle = trim($device->mqttTopic, '/');
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
