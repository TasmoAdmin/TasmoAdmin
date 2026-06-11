<?php

namespace TasmoAdmin\Mqtt;

class SystemTimeProvider implements TimeProviderInterface
{
    public function now(): float
    {
        return microtime(true);
    }

    public function sleep(int $microseconds): void
    {
        usleep($microseconds);
    }
}
