<?php

namespace TasmoAdmin\Mqtt;

class MqttDiscoveryRequest
{
    public function __construct(
        public string $host,
        public int $port,
        public string $username,
        public string $password,
        public string $commandPrefix,
        public string $statPrefix,
        public string $telePrefix,
        public array $subscriptionFilters,
        public int $timeoutSeconds,
        public int $httpPort,
        public string $httpUsername,
        public string $httpPassword
    ) {}
}
