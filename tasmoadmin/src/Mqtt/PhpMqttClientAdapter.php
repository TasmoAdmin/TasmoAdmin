<?php

namespace TasmoAdmin\Mqtt;

use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;

class PhpMqttClientAdapter implements MqttClientInterface
{
    public function __construct(private MqttClient $client) {}

    public function connect(?string $username, ?string $password, int $timeoutSeconds): void
    {
        $settings = new ConnectionSettings();
        $settings = $settings->setConnectTimeout(max($timeoutSeconds, 1));
        $settings = $settings->setSocketTimeout(max($timeoutSeconds, 1));

        if (null !== $username && '' !== $username) {
            $settings = $settings->setUsername($username);
        }

        if (null !== $password && '' !== $password) {
            $settings = $settings->setPassword($password);
        }

        $this->client->connect($settings, true);
    }

    public function subscribe(string $topicFilter, callable $callback): void
    {
        $this->client->subscribe($topicFilter, $callback, MqttClient::QOS_AT_MOST_ONCE);
    }

    public function publish(string $topic, string $payload): void
    {
        $this->client->publish($topic, $payload, MqttClient::QOS_AT_MOST_ONCE, false);
    }

    public function loopOnce(float $loopStartedAt): void
    {
        $this->client->loopOnce($loopStartedAt);
    }

    public function disconnect(): void
    {
        if ($this->client->isConnected()) {
            $this->client->disconnect();
        }
    }
}
