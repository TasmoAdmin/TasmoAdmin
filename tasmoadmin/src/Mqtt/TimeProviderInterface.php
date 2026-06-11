<?php

namespace TasmoAdmin\Mqtt;

interface TimeProviderInterface
{
    public function now(): float;

    public function sleep(int $microseconds): void;
}
