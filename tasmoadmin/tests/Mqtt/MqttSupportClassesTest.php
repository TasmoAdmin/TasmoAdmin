<?php

namespace Tests\TasmoAdmin\Mqtt;

use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;
use PHPUnit\Framework\TestCase;
use TasmoAdmin\Mqtt\MqttDiscoveryRequest;
use TasmoAdmin\Mqtt\MqttDiscoveryResult;
use TasmoAdmin\Mqtt\PhpMqttClientAdapter;
use TasmoAdmin\Mqtt\PhpMqttClientFactory;
use TasmoAdmin\Mqtt\SystemTimeProvider;

class MqttSupportClassesTest extends TestCase
{
    public function testDiscoveryRequestStoresConstructorArguments(): void
    {
        $request = new MqttDiscoveryRequest(
            'broker.local',
            1883,
            'mqtt-user',
            'mqtt-pass',
            'cmnd',
            'stat',
            'tele',
            ['tele/+/LWT'],
            5,
            80,
            'admin',
            'secret'
        );

        self::assertSame('broker.local', $request->host);
        self::assertSame(1883, $request->port);
        self::assertSame('mqtt-user', $request->username);
        self::assertSame(['tele/+/LWT'], $request->subscriptionFilters);
        self::assertSame('admin', $request->httpUsername);
    }

    public function testDiscoveryResultDefaultsAndStoresPayloads(): void
    {
        $empty = new MqttDiscoveryResult();
        self::assertSame([], $empty->updatedDevices);
        self::assertSame([], $empty->newDevices);
        self::assertSame([], $empty->offlineTopics);
        self::assertSame([], $empty->conflicts);

        $device = new \stdClass();
        $result = new MqttDiscoveryResult(
            [['id' => 1]],
            [$device],
            [['mqttTopic' => 'offline/topic']],
            [['mqttTopic' => 'duplicate/topic']]
        );

        self::assertSame([['id' => 1]], $result->updatedDevices);
        self::assertSame([$device], $result->newDevices);
        self::assertSame([['mqttTopic' => 'offline/topic']], $result->offlineTopics);
        self::assertSame([['mqttTopic' => 'duplicate/topic']], $result->conflicts);
    }

    public function testAdapterConnectPassesTimeoutAndCredentials(): void
    {
        $client = $this->createMock(MqttClient::class);
        $client->expects(self::once())
            ->method('connect')
            ->with(
                self::callback(function (ConnectionSettings $settings): bool {
                    self::assertSame(1, $settings->getConnectTimeout());
                    self::assertSame(1, $settings->getSocketTimeout());
                    self::assertSame('mqtt-user', $settings->getUsername());
                    self::assertSame('mqtt-pass', $settings->getPassword());

                    return true;
                }),
                true
            )
        ;

        $adapter = new PhpMqttClientAdapter($client);
        $adapter->connect('mqtt-user', 'mqtt-pass', 0);
    }

    public function testAdapterConnectSkipsEmptyCredentials(): void
    {
        $client = $this->createMock(MqttClient::class);
        $client->expects(self::once())
            ->method('connect')
            ->with(
                self::callback(function (ConnectionSettings $settings): bool {
                    self::assertNull($settings->getUsername());
                    self::assertNull($settings->getPassword());
                    self::assertSame(4, $settings->getConnectTimeout());
                    self::assertSame(4, $settings->getSocketTimeout());

                    return true;
                }),
                true
            )
        ;

        $adapter = new PhpMqttClientAdapter($client);
        $adapter->connect('', '', 4);
    }

    public function testAdapterDelegatesSubscribePublishAndLoop(): void
    {
        $client = $this->createMock(MqttClient::class);
        $callback = static function (): void {};

        $client->expects(self::once())
            ->method('subscribe')
            ->with('tele/+/LWT', $callback, MqttClient::QOS_AT_MOST_ONCE)
        ;
        $client->expects(self::once())
            ->method('publish')
            ->with('cmnd/device/Power', 'ON', MqttClient::QOS_AT_MOST_ONCE, false)
        ;
        $client->expects(self::once())
            ->method('loopOnce')
            ->with(123.45)
        ;

        $adapter = new PhpMqttClientAdapter($client);
        $adapter->subscribe('tele/+/LWT', $callback);
        $adapter->publish('cmnd/device/Power', 'ON');
        $adapter->loopOnce(123.45);
    }

    public function testAdapterDisconnectOnlyDisconnectsWhenConnected(): void
    {
        $connectedClient = $this->createMock(MqttClient::class);
        $connectedClient->expects(self::once())->method('isConnected')->willReturn(true);
        $connectedClient->expects(self::once())->method('disconnect');

        $disconnectedClient = $this->createMock(MqttClient::class);
        $disconnectedClient->expects(self::once())->method('isConnected')->willReturn(false);
        $disconnectedClient->expects(self::never())->method('disconnect');

        $connectedAdapter = new PhpMqttClientAdapter($connectedClient);
        $connectedAdapter->disconnect();

        $disconnectedAdapter = new PhpMqttClientAdapter($disconnectedClient);
        $disconnectedAdapter->disconnect();
    }

    public function testFactoryCreatesAdapterWithConfiguredBrokerSettings(): void
    {
        $factory = new PhpMqttClientFactory();
        $adapter = $factory->create('broker.local', 1884);

        self::assertInstanceOf(PhpMqttClientAdapter::class, $adapter);

        $clientProperty = new \ReflectionProperty($adapter, 'client');
        $client = $clientProperty->getValue($adapter);

        $hostProperty = new \ReflectionProperty($client, 'host');
        $portProperty = new \ReflectionProperty($client, 'port');
        $clientIdProperty = new \ReflectionProperty($client, 'clientId');

        self::assertSame('broker.local', $hostProperty->getValue($client));
        self::assertSame(1884, $portProperty->getValue($client));
        self::assertStringStartsWith('tasmoadmin-discovery-', $clientIdProperty->getValue($client));
    }

    public function testSystemTimeProviderReturnsCurrentTimeAndSleeps(): void
    {
        $provider = new SystemTimeProvider();
        $before = microtime(true);
        $now = $provider->now();
        $provider->sleep(1000);
        $after = microtime(true);

        self::assertGreaterThanOrEqual($before, $now);
        self::assertLessThanOrEqual($after, $now);
        self::assertGreaterThan($before, $after);
    }
}
