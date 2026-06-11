<?php

namespace TasmoAdmin\Mqtt;

interface MqttClientFactoryInterface
{
    public function create(string $host, int $port): MqttClientInterface;
}
