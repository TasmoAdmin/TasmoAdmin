<?php

declare(strict_types=1);

namespace Tests\TasmoAdmin\Mqtt;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use TasmoAdmin\DevicePasswordCipher;
use TasmoAdmin\DevicePasswordKeyProvider;
use TasmoAdmin\DeviceRepository;
use TasmoAdmin\Mqtt\MqttClientFactoryInterface;
use TasmoAdmin\Mqtt\MqttClientInterface;
use TasmoAdmin\Mqtt\MqttDiscoveryRequest;
use TasmoAdmin\Mqtt\MqttDiscoveryService;
use TasmoAdmin\Mqtt\TimeProviderInterface;
use TasmoAdmin\Tasmota\ResponseParser;

class MqttDiscoveryServiceTest extends TestCase
{
    private vfsStreamDirectory $root;

    protected function setUp(): void
    {
        $this->root = vfsStream::setup('mqtt-discovery');
        putenv(DevicePasswordKeyProvider::ENV_NAME.'='.base64_encode(random_bytes(DevicePasswordKeyProvider::KEY_LENGTH)));
    }

    protected function tearDown(): void
    {
        putenv(DevicePasswordKeyProvider::ENV_NAME);
    }

    public function testScanRefreshesExistingDeviceByMqttTopic(): void
    {
        $repository = $this->createRepository();
        $repository->addDevices([[
            'device_name' => ['kitchen-plug'],
            'device_ip' => '192.168.1.20',
            'device_port' => 8080,
            'device_mqtt_topic' => 'kitchen-plug',
        ]], 'user', 'pass');

        $client = new FakeMqttClient([
            ['tele/kitchen-plug/LWT', 'Online'],
            ['stat/kitchen-plug/STATUS0', $this->buildStatusPayload('192.168.1.44', ['Kitchen Plug'])],
        ]);
        $service = $this->createService($repository, $client);

        $result = $service->scan($this->createRequest());

        self::assertCount(1, $result->updatedDevices);
        self::assertSame('192.168.1.44', $repository->getDeviceById(1)->ip);
        self::assertSame('kitchen-plug', $repository->getDeviceById(1)->mqttTopic);
        self::assertSame(8080, $repository->getDeviceById(1)->port);
        self::assertSame([['cmnd/kitchen-plug/STATUS', '0']], $client->publishedMessages);
    }

    public function testScanBackfillsLegacyDeviceUsingAddressMatchAndCustomPrefixes(): void
    {
        $repository = $this->createRepository();
        $repository->addDevices([[
            'device_name' => ['boiler'],
            'device_ip' => '192.168.1.50',
            'device_port' => 8081,
        ]], 'user', 'pass');

        $client = new FakeMqttClient([
            ['telemetry/boiler/LWT', 'Online'],
            ['status/boiler/STATUS0', $this->buildStatusPayload('192.168.1.50', ['Boiler'])],
        ]);
        $service = $this->createService($repository, $client);

        $result = $service->scan(new MqttDiscoveryRequest(
            'broker.local',
            1883,
            '',
            '',
            'command',
            'status',
            'telemetry',
            ['telemetry/+/LWT'],
            1,
            8081,
            'admin',
            'secret'
        ));

        self::assertCount(1, $result->updatedDevices);
        self::assertSame('boiler', $repository->getDeviceById(1)->mqttTopic);
        self::assertSame([['command/boiler/STATUS', '0']], $client->publishedMessages);
    }

    public function testScanReturnsNewOfflineAndConflictBuckets(): void
    {
        $repository = $this->createRepository();
        $repository->addDevices([
            [
                'device_name' => ['dup-1'],
                'device_ip' => '192.168.1.10',
                'device_mqtt_topic' => 'dup',
            ],
            [
                'device_name' => ['dup-2'],
                'device_ip' => '192.168.1.11',
                'device_mqtt_topic' => 'dup',
            ],
        ], 'user', 'pass');

        $client = new FakeMqttClient([
            ['tele/new-device/LWT', 'Online'],
            ['stat/new-device/STATUS0', $this->buildStatusPayload('192.168.1.88', ['New Device'])],
            ['tele/offline-device/LWT', 'Offline'],
            ['tele/dup/LWT', 'Online'],
            ['stat/dup/STATUS0', $this->buildStatusPayload('192.168.1.77', ['Duplicate'])],
        ]);
        $service = $this->createService($repository, $client);

        $result = $service->scan($this->createRequest());

        self::assertCount(1, $result->newDevices);
        self::assertSame('new-device', $result->newDevices[0]->TasmoAdminMqttTopic);
        self::assertCount(1, $result->offlineTopics);
        self::assertSame('offline-device', $result->offlineTopics[0]['mqttTopic']);
        self::assertCount(1, $result->conflicts);
        self::assertSame('dup', $result->conflicts[0]['mqttTopic']);
    }

    public function testScanMarksExistingTopicAsConflictWhenStatusAddressBelongsToAnotherStoredDevice(): void
    {
        $repository = $this->createRepository();
        $repository->addDevices([
            [
                'device_name' => ['topic-owner'],
                'device_ip' => '192.168.1.20',
                'device_port' => 80,
                'device_mqtt_topic' => 'shared-topic',
            ],
            [
                'device_name' => ['other-device'],
                'device_ip' => '192.168.1.30',
                'device_port' => 80,
            ],
        ], 'user', 'pass');

        $client = new FakeMqttClient([
            ['tele/shared-topic/LWT', 'Online'],
            ['stat/shared-topic/STATUS0', $this->buildStatusPayload('192.168.1.30', ['Other Device'])],
        ]);
        $service = $this->createService($repository, $client);

        $result = $service->scan($this->createRequest());

        self::assertCount(0, $result->updatedDevices);
        self::assertCount(1, $result->conflicts);
        self::assertSame('shared-topic', $result->conflicts[0]['mqttTopic']);
        self::assertSame('192.168.1.20', $repository->getDeviceById(1)->ip);
        self::assertSame('192.168.1.30', $repository->getDeviceById(2)->ip);
    }

    public function testScanMarksSecondLegacyTopicForSameAddressAsConflict(): void
    {
        $repository = $this->createRepository();
        $repository->addDevices([[
            'device_name' => ['heater'],
            'device_ip' => '192.168.1.60',
            'device_port' => 80,
        ]], 'user', 'pass');

        $client = new FakeMqttClient([
            ['tele/old-topic/LWT', 'Online'],
            ['stat/old-topic/STATUS0', $this->buildStatusPayload('192.168.1.60', ['Heater'])],
            ['tele/new-topic/LWT', 'Online'],
            ['stat/new-topic/STATUS0', $this->buildStatusPayload('192.168.1.60', ['Heater'])],
        ]);
        $service = $this->createService($repository, $client);

        $result = $service->scan($this->createRequest());

        self::assertCount(1, $result->updatedDevices);
        self::assertSame('old-topic', $repository->getDeviceById(1)->mqttTopic);
        self::assertCount(1, $result->conflicts);
        self::assertSame('new-topic', $result->conflicts[0]['mqttTopic']);
    }

    public function testScanRebindsExistingDeviceWhenTopicChangesButAddressStaysTheSame(): void
    {
        $repository = $this->createRepository();
        $repository->addDevices([[
            'device_name' => ['pump'],
            'device_ip' => '192.168.1.70',
            'device_port' => 80,
            'device_mqtt_topic' => 'old-topic',
        ]], 'user', 'pass');

        $client = new FakeMqttClient([
            ['tele/new-topic/LWT', 'Online'],
            ['stat/new-topic/STATUS0', $this->buildStatusPayload('192.168.1.70', ['Pump'])],
        ]);
        $service = $this->createService($repository, $client);

        $result = $service->scan($this->createRequest());

        self::assertCount(1, $result->updatedDevices);
        self::assertSame('new-topic', $repository->getDeviceById(1)->mqttTopic);
        self::assertSame(80, $repository->getDeviceById(1)->port);
        self::assertCount(0, $result->newDevices);
    }

    public function testScanSupportsMultiSegmentPrefixesAndNestedTopics(): void
    {
        $repository = $this->createRepository();
        $client = new FakeMqttClient([
            ['home/tele/downstairs/lamp/LWT', 'Online'],
            ['home/stat/downstairs/lamp/STATUS0', $this->buildStatusPayload('192.168.1.81', ['Downstairs Lamp'])],
        ]);
        $service = $this->createService($repository, $client);

        $result = $service->scan(new MqttDiscoveryRequest(
            'broker.local',
            1883,
            '',
            '',
            'home/cmnd',
            'home/stat',
            'home/tele',
            ['home/tele/+/+/LWT'],
            1,
            80,
            'admin',
            'secret'
        ));

        self::assertCount(1, $result->newDevices);
        self::assertSame('downstairs/lamp', $result->newDevices[0]->TasmoAdminMqttTopic);
        self::assertSame([['home/cmnd/downstairs/lamp/STATUS', '0']], $client->publishedMessages);
    }

    public function testScanMarksInvalidStatusPayloadAsOffline(): void
    {
        $repository = $this->createRepository();
        $client = new FakeMqttClient([
            ['tele/broken-device/LWT', 'Online'],
            ['stat/broken-device/STATUS0', json_encode([
                'Status' => [
                    'FriendlyName' => ['Broken Device'],
                ],
                'StatusNET' => [],
            ], JSON_THROW_ON_ERROR)],
        ]);
        $service = $this->createService($repository, $client);

        $result = $service->scan($this->createRequest());

        self::assertCount(0, $result->newDevices);
        self::assertCount(1, $result->offlineTopics);
        self::assertSame('broken-device', $result->offlineTopics[0]['mqttTopic']);
        self::assertSame('invalid-status0-response', $result->offlineTopics[0]['reason']);
    }

    public function testScanMarksAmbiguousLegacyAddressAsConflict(): void
    {
        $repository = $this->createRepository();
        $repository->addDevices([
            [
                'device_name' => ['legacy-1'],
                'device_ip' => '192.168.1.90',
                'device_port' => 80,
            ],
            [
                'device_name' => ['legacy-2'],
                'device_ip' => '192.168.1.90',
                'device_port' => 80,
            ],
        ], 'user', 'pass');

        $client = new FakeMqttClient([
            ['tele/ambiguous-topic/LWT', 'Online'],
            ['stat/ambiguous-topic/STATUS0', $this->buildStatusPayload('192.168.1.90', ['Ambiguous Device'])],
        ]);
        $service = $this->createService($repository, $client);

        $result = $service->scan($this->createRequest());

        self::assertCount(0, $result->updatedDevices);
        self::assertCount(0, $result->newDevices);
        self::assertCount(1, $result->conflicts);
        self::assertSame('ambiguous-topic', $result->conflicts[0]['mqttTopic']);
        self::assertSame('legacy-address-ambiguous', $result->conflicts[0]['reason']);
    }

    public function testScanDisconnectsClientWhenLoopFails(): void
    {
        $repository = $this->createRepository();
        $client = new DisconnectTrackingMqttClient();
        $service = $this->createService($repository, $client);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('loop failed');

        try {
            $service->scan($this->createRequest());
        } finally {
            self::assertTrue($client->disconnected);
        }
    }

    private function createService(DeviceRepository $repository, MqttClientInterface $client): MqttDiscoveryService
    {
        return new MqttDiscoveryService(
            $repository,
            new ResponseParser(),
            new class($client) implements MqttClientFactoryInterface {
                public function __construct(private MqttClientInterface $client) {}

                public function create(string $host, int $port): MqttClientInterface
                {
                    return $this->client;
                }
            },
            new class implements TimeProviderInterface {
                private float $now = 0.0;

                public function now(): float
                {
                    return $this->now;
                }

                public function sleep(int $microseconds): void
                {
                    $this->now += $microseconds / 1_000_000;
                }
            }
        );
    }

    private function createRequest(): MqttDiscoveryRequest
    {
        return new MqttDiscoveryRequest(
            'broker.local',
            1883,
            '',
            '',
            'cmnd',
            'stat',
            'tele',
            ['tele/+/LWT'],
            1,
            80,
            'admin',
            'secret'
        );
    }

    private function createRepository(): DeviceRepository
    {
        $deviceFile = $this->root->url().'/devices.csv';
        touch($deviceFile);

        return new DeviceRepository(
            $deviceFile,
            $this->getTmpDir(),
            new DevicePasswordCipher(new DevicePasswordKeyProvider($this->getDataDir()))
        );
    }

    private function getTmpDir(): string
    {
        $tmpDir = $this->root->url().'/tmp/';
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir);
        }

        return $tmpDir;
    }

    private function getDataDir(): string
    {
        $dataDir = $this->root->url().'/data/';
        if (!is_dir($dataDir)) {
            mkdir($dataDir);
        }

        return $dataDir;
    }

    private function buildStatusPayload(string $ip, array $friendlyNames): string
    {
        return json_encode([
            'Status' => [
                'FriendlyName' => $friendlyNames,
            ],
            'StatusNET' => [
                'IPAddress' => $ip,
            ],
            'StatusSTS' => [
                'POWER' => 'ON',
            ],
        ], JSON_THROW_ON_ERROR);
    }
}

class FakeMqttClient implements MqttClientInterface
{
    /** @var array<int, array{0: string, 1: string}> */
    public array $publishedMessages = [];

    /** @var array<int, array{0: string, 1: string}> */
    private array $messages;

    /** @var array<int, array{filter: string, callback: callable}> */
    private array $subscriptions = [];

    public function __construct(array $messages)
    {
        $this->messages = $messages;
    }

    public function connect(?string $username, ?string $password, int $timeoutSeconds): void {}

    public function subscribe(string $topicFilter, callable $callback): void
    {
        $this->subscriptions[] = ['filter' => $topicFilter, 'callback' => $callback];
    }

    public function publish(string $topic, string $payload): void
    {
        $this->publishedMessages[] = [$topic, $payload];
    }

    public function loopOnce(float $loopStartedAt): void
    {
        if ([] === $this->messages) {
            return;
        }

        [$topic, $payload] = array_shift($this->messages);
        foreach ($this->subscriptions as $subscription) {
            if ($this->matches($subscription['filter'], $topic)) {
                ($subscription['callback'])($topic, $payload, false, []);
            }
        }
    }

    public function disconnect(): void {}

    private function matches(string $filter, string $topic): bool
    {
        $filterParts = explode('/', trim($filter, '/'));
        $topicParts = explode('/', trim($topic, '/'));

        foreach ($filterParts as $index => $filterPart) {
            if ('#' === $filterPart) {
                return $index === count($filterParts) - 1;
            }

            if (!array_key_exists($index, $topicParts)) {
                return false;
            }

            if ('+' === $filterPart) {
                continue;
            }

            if ($filterPart !== $topicParts[$index]) {
                return false;
            }
        }

        return count($filterParts) === count($topicParts);
    }
}

class DisconnectTrackingMqttClient implements MqttClientInterface
{
    public bool $disconnected = false;

    public function connect(?string $username, ?string $password, int $timeoutSeconds): void {}

    public function subscribe(string $topicFilter, callable $callback): void {}

    public function publish(string $topic, string $payload): void {}

    public function loopOnce(float $loopStartedAt): void
    {
        throw new \RuntimeException('loop failed');
    }

    public function disconnect(): void
    {
        $this->disconnected = true;
    }
}
