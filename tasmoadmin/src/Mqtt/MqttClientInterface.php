<?php

namespace TasmoAdmin\Mqtt;

interface MqttClientInterface
{
    public function connect(?string $username, ?string $password, int $timeoutSeconds): void;

    public function subscribe(string $topicFilter, callable $callback): void;

    public function publish(string $topic, string $payload): void;

    public function loopOnce(float $loopStartedAt): void;

    public function disconnect(): void;
}
