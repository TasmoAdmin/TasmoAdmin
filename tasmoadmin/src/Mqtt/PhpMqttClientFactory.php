<?php

namespace TasmoAdmin\Mqtt;

use PhpMqtt\Client\MqttClient;

class PhpMqttClientFactory implements MqttClientFactoryInterface
{
    public function create(string $host, int $port): MqttClientInterface
    {
        return new PhpMqttClientAdapter(
            new MqttClient(
                $host,
                $port,
                'tasmoadmin-discovery-'.bin2hex(random_bytes(5)),
                MqttClient::MQTT_3_1_1
            )
        );
    }
}
