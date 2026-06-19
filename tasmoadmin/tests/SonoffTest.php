<?php

namespace Tests\TasmoAdmin;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TasmoAdmin\Config;
use TasmoAdmin\Device;
use TasmoAdmin\DeviceFactory;
use TasmoAdmin\DevicePasswordCipher;
use TasmoAdmin\DevicePasswordKeyProvider;
use TasmoAdmin\DeviceRepository;
use TasmoAdmin\Sonoff;

class SonoffTest extends TestCase
{
    private const TEST_KEY = 'MDEyMzQ1Njc4OWFiY2RlZjAxMjM0NTY3ODlhYmNkZWY=';

    private vfsStreamDirectory $root;

    protected function setUp(): void
    {
        $this->root = vfsStream::setup('config');
        putenv(DevicePasswordKeyProvider::ENV_NAME.'='.self::TEST_KEY);
    }

    protected function tearDown(): void
    {
        putenv(DevicePasswordKeyProvider::ENV_NAME);
    }

    public function testbuildCmndUrlCredentials(): void
    {
        $device = DeviceFactory::fromArray([0, 'socket-1', '192.168.1.1', 'user', 'pass']);
        $sonoff = new Sonoff($this->getTestDeviceRepository(), $this->getClient(), $this->getTestConfig());
        $url = $sonoff->buildCmndUrl($device, Sonoff::COMMAND_INFO_STATUS_ALL);
        self::assertEquals('http://192.168.1.1:80/cm?user=user&password=pass&cmnd=status+0', $url);
    }

    public function testbuildCmndUrlNonStandardPort(): void
    {
        $device = DeviceFactory::fromArray([0, 'socket-1', '192.168.1.1', 'user', 'pass', null, null, null, null, null, null, 5000]);
        $sonoff = new Sonoff($this->getTestDeviceRepository(), $this->getClient(), $this->getTestConfig());
        $url = $sonoff->buildCmndUrl($device, Sonoff::COMMAND_INFO_STATUS_ALL);
        self::assertEquals('http://192.168.1.1:5000/cm?user=user&password=pass&cmnd=status+0', $url);
    }

    public function testbuildCmndUrlNoCredentials(): void
    {
        $device = DeviceFactory::fromArray([0, 'socket-1', '192.168.1.1']);
        $sonoff = new Sonoff($this->getTestDeviceRepository(), $this->getClient(), $this->getTestConfig());
        $url = $sonoff->buildCmndUrl($device, Sonoff::COMMAND_INFO_STATUS_ALL);
        self::assertEquals('http://192.168.1.1:80/cm?cmnd=status+0', $url);
    }

    public function testBackupBasicAuthUrlEncodesSpecialCharacters(): void
    {
        $device = DeviceFactory::fromArray([0, 'socket-1', '192.168.1.8', 'user+name', 'pa ss:@']);
        $transactions = [];
        $history = Middleware::history($transactions);
        $mock = new MockHandler([new Response(200, [], 'backup-data')]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $client = new Client(['handler' => $handlerStack]);
        $sonoff = new Sonoff($this->getTestDeviceRepository(), $client, $this->getTestConfig());

        $sonoff->backup($device, $this->getBackupDir());

        self::assertCount(1, $transactions);
        self::assertSame(
            'http://user%2Bname:pa%20ss%3A%40@192.168.1.8/dl',
            (string) $transactions[0]['request']->getUri()
        );
    }

    public function testGetAllStatusValid(): void
    {
        $device = DeviceFactory::fromArray([0, 'socket-1', '192.168.1.8']);
        $sonoff = new Sonoff($this->getTestDeviceRepository(), $this->getClient([
            new Response(200, [], TestUtils::loadFixture('response-valid.json')),
        ]), $this->getTestConfig());
        $result = $sonoff->getAllStatus($device);
        self::assertEquals('socket-1', $result->Status->DeviceName);
    }

    public function testGetAllStatusUnauthorized(): void
    {
        $device = DeviceFactory::fromArray([0, 'socket-1', '192.168.1.8']);
        $sonoff = new Sonoff($this->getTestDeviceRepository(), $this->getClient([
            new Response(401, [], TestUtils::loadFixture('response-unauthorized.json')),
        ]), $this->getTestConfig());
        $result = $sonoff->getAllStatus($device);
        self::assertStringContainsString('401 Unauthorized', $result->ERROR);
    }

    public function testSearch(): void
    {
        $sonoff = new Sonoff($this->getTestDeviceRepository(), $this->getClient([
            new Response(200, [], TestUtils::loadFixture('response-valid.json')),
            new Response(401, [], TestUtils::loadFixture('response-unauthorized.json')),
        ]), $this->getTestConfig());

        $devices = [];
        foreach (range(1, 2) as $count) {
            $device = DeviceFactory::fromArray([$count, sprintf('socket-%d', $count), sprintf('192.168.1.%d', $count)]);
            $devices[] = $sonoff->buildCmndUrl($device, Sonoff::COMMAND_INFO_STATUS_ALL);
        }

        $result = $sonoff->search($devices);
        self::assertCount(1, $result);
    }

    public function testDecodeOptionsIncludesMqttDiscovery(): void
    {
        $sonoff = new Sonoff($this->getTestDeviceRepository(), $this->getClient(), $this->getTestConfig());

        $result = $sonoff->decodeOptions('80000');

        self::assertSame('HASS discovery', $result->SetOption19->desc);
        self::assertSame(1, $result->SetOption19->value);
    }

    public function testDecodeOptionsReturnsFalseForEmptyInput(): void
    {
        $sonoff = new Sonoff($this->getTestDeviceRepository(), $this->getClient(), $this->getTestConfig());

        self::assertFalse($sonoff->decodeOptions(''));
    }

    public function testDecodeOptionsAcceptsArrayInput(): void
    {
        $sonoff = new Sonoff($this->getTestDeviceRepository(), $this->getClient(), $this->getTestConfig());

        $result = $sonoff->decodeOptions(['80000']);

        self::assertSame('HASS discovery', $result->SetOption19->desc);
        self::assertSame(1, $result->SetOption19->value);
    }

    public function testGetDevicesBasic(): void
    {
        $mockRepository = $this->createMock(DeviceRepository::class);
        $mockRepository->method('getDevices')->willReturn([
            new Device(3, ['socket-3'], '192.168.1.3', 'user', 'pass', 'img', 3, false, false, false, []),
            new Device(2, ['socket-2'], '192.168.1.2', 'user', 'pass', 'img', 2, false, false, false, []),
            new Device(1, ['socket-1'], '192.168.1.1', 'user', 'pass', 'img', 1, false, false, false, []),
        ]);

        $sonoff = new Sonoff($mockRepository, $this->getClient(), $this->getTestConfig());
        $devices = $sonoff->getDevices();
        self::assertEquals(1, $devices[1]->position);
        self::assertEquals(2, $devices[2]->position);
        self::assertEquals(3, $devices[3]->position);
    }

    public function testGetDevicesMissingPosition(): void
    {
        $mockRepository = $this->createMock(DeviceRepository::class);
        $mockRepository->method('getDevices')->willReturn([
            new Device(1, ['socket-1'], '192.168.1.1', 'user', 'pass', 'img', 1, false, false, false, []),
            new Device(2, ['socket-2'], '192.168.1.1', 'user', 'pass', 'img', 0, false, false, false, []),
        ]);

        $sonoff = new Sonoff($mockRepository, $this->getClient(), $this->getTestConfig());
        $devices = $sonoff->getDevices();
        self::assertEquals(1, $devices[1]->position);
        self::assertEquals(['socket-1'], $devices[1]->names);
        self::assertEquals(2, $devices[2]->position);
        self::assertEquals(['socket-2'], $devices[2]->names);
    }

    public function testGetDevicesOverlapPositionBasic(): void
    {
        $mockRepository = $this->createMock(DeviceRepository::class);
        $mockRepository->method('getDevices')->willReturn([
            new Device(1, ['socket-1'], '192.168.1.1', 'user', 'pass', 'img', 1, false, false, false, []),
            new Device(2, ['socket-2'], '192.168.1.1', 'user', 'pass', 'img', 1, false, false, false, []),
            new Device(3, ['socket-3'], '192.168.1.1', 'user', 'pass', 'img', 1, false, false, false, []),
        ]);

        $sonoff = new Sonoff($mockRepository, $this->getClient(), $this->getTestConfig());
        $devices = $sonoff->getDevices();
        self::assertEquals(1, $devices[1]->position);
        self::assertEquals(['socket-1'], $devices[1]->names);
        self::assertEquals(2, $devices[2]->position);
        self::assertEquals(['socket-2'], $devices[2]->names);
        self::assertEquals(3, $devices[3]->position);
        self::assertEquals(['socket-3'], $devices[3]->names);
    }

    public function testGetDevicesOverlapPositionComplex(): void
    {
        $mockRepository = $this->createMock(DeviceRepository::class);
        $mockRepository->method('getDevices')->willReturn([
            new Device(1, ['socket-1'], '192.168.1.1', 'user', 'pass', 'img', 2, false, false, false, []),
            new Device(2, ['socket-2'], '192.168.1.1', 'user', 'pass', 'img', 1, false, false, false, []),
            new Device(3, ['socket-3'], '192.168.1.1', 'user', 'pass', 'img', 2, false, false, false, []),
            new Device(4, ['socket-4'], '192.168.1.1', 'user', 'pass', 'img', 1, false, false, false, []),
        ]);

        $sonoff = new Sonoff($mockRepository, $this->getClient(), $this->getTestConfig());
        $devices = $sonoff->getDevices();
        self::assertEquals(1, $devices[1]->position);
        self::assertEquals(['socket-2'], $devices[1]->names);
        self::assertEquals(2, $devices[2]->position);
        self::assertEquals(['socket-1'], $devices[2]->names);
        self::assertEquals(3, $devices[3]->position);
        self::assertEquals(['socket-3'], $devices[3]->names);
        self::assertEquals(4, $devices[4]->position);
        self::assertEquals(['socket-4'], $devices[4]->names);
    }

    public function testBackup(): void
    {
        $device = DeviceFactory::fromArray([0, 'socket-1', '192.168.1.8']);
        $fileContent = 'fake-backup';
        $sonoff = new Sonoff($this->getTestDeviceRepository(), $this->getClient([
            new Response(200, [], $fileContent),
        ]), $this->getTestConfig());
        $location = $sonoff->backup($device, $this->getBackupDir());
        $this->assertTrue($this->root->hasChild('backups/'.$device->getBackupName()));
        $this->assertEquals($fileContent, file_get_contents($location));
    }

    public function testBackupReplacesExistingFile(): void
    {
        $device = DeviceFactory::fromArray([0, 'socket-1', '192.168.1.8']);
        $backupDir = $this->getBackupDir();
        $existingFile = $backupDir.$device->getBackupName();
        file_put_contents($existingFile, 'old-backup');

        $sonoff = new Sonoff($this->getTestDeviceRepository(), $this->getClient([
            new Response(200, [], 'new-backup'),
        ]), $this->getTestConfig());

        $location = $sonoff->backup($device, $backupDir);

        self::assertSame($existingFile, $location);
        self::assertSame('new-backup', file_get_contents($location));
    }

    public function testRestoreUsesWebGetConfigCommand(): void
    {
        $device = DeviceFactory::fromArray([0, 'socket-1', '192.168.1.8']);
        $transactions = [];
        $history = Middleware::history($transactions);
        $mock = new MockHandler([new Response(200, [], '{"WebGetConfig":"Started"}')]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $client = new Client(['handler' => $handlerStack]);
        $sonoff = new Sonoff($this->getTestDeviceRepository(), $client, $this->getTestConfig());

        $result = $sonoff->restore($device, 'http://192.168.1.1:8080/_BASEURL_/actions?downloadRestore=abc123');

        self::assertSame('Started', $result->WebGetConfig);
        self::assertCount(1, $transactions);
        self::assertSame(
            'http://192.168.1.8/cm?cmnd=WebGetConfig+http%3A%2F%2F192.168.1.1%3A8080%2F_BASEURL_%2Factions%3FdownloadRestore%3Dabc123',
            (string) $transactions[0]['request']->getUri()
        );
    }

    public function testRepositoryBackedEncryptedPasswordsStillWorkForUrlsAndBackup(): void
    {
        $repository = $this->getTestDeviceRepository();
        $repository->addDevices(
            [['device_name' => ['socket-1'], 'device_ip' => '192.168.1.8']],
            'user',
            'pass'
        );

        $device = $repository->getDeviceById(1);
        $transactions = [];
        $history = Middleware::history($transactions);
        $mock = new MockHandler([new Response(200, [], 'fake-backup')]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $client = new Client(['handler' => $handlerStack]);

        $sonoff = new Sonoff($repository, $client, $this->getTestConfig());

        self::assertSame(
            'http://192.168.1.8:80/cm?user=user&password=pass&cmnd=status+0',
            $sonoff->buildCmndUrl($device, Sonoff::COMMAND_INFO_STATUS_ALL)
        );

        $sonoff->backup($device, $this->getBackupDir());

        self::assertCount(1, $transactions);
        self::assertSame('http://user:pass@192.168.1.8/dl', (string) $transactions[0]['request']->getUri());
    }

    public function testGetNtpStatusReturnsEmptyStringWhenCommandIsUnknown(): void
    {
        $device = DeviceFactory::fromArray([0, 'socket-1', '192.168.1.8']);
        $sonoff = new Sonoff($this->getTestDeviceRepository(), $this->getClient([
            new Response(200, [], '{"Command":"Unknown"}'),
        ]), $this->getTestConfig());

        self::assertSame('', $sonoff->getNTPStatus($device));
    }

    public function testGetNtpStatusReturnsParsedStatusWhenAvailable(): void
    {
        $device = DeviceFactory::fromArray([0, 'socket-1', '192.168.1.8']);
        $sonoff = new Sonoff($this->getTestDeviceRepository(), $this->getClient([
            new Response(200, [], '{"NtpServer1":"pool.ntp.org"}'),
        ]), $this->getTestConfig());

        self::assertSame('pool.ntp.org', $sonoff->getNTPStatus($device)->NtpServer1);
    }

    public function testGetAllStatusReturnsCurlErrorWhenRequestFails(): void
    {
        $device = DeviceFactory::fromArray([0, 'socket-1', '192.168.1.8']);
        $request = new Request('GET', 'http://192.168.1.8:80/cm?cmnd=status+0');
        $client = $this->createMock(Client::class);
        $client->expects(self::once())
            ->method('get')
            ->with('http://192.168.1.8:80/cm?cmnd=status+0')
            ->willThrowException(new RequestException('timeout', $request))
        ;

        $sonoff = new Sonoff($this->getTestDeviceRepository(), $client, $this->getTestConfig());
        $result = $sonoff->getAllStatus($device);

        self::assertStringContainsString('API_CURL_ERROR:', $result->ERROR);
        self::assertStringContainsString('timeout', $result->ERROR);
    }

    #[DataProvider('mqttValueMethodProvider')]
    public function testMqttValueReadersReturnParsedValues(string $method, string $field, string $expected): void
    {
        $device = DeviceFactory::fromArray([0, 'socket-1', '192.168.1.8']);
        $sonoff = new Sonoff($this->getTestDeviceRepository(), $this->getClient([
            new Response(200, [], json_encode([$field => $expected], JSON_THROW_ON_ERROR)),
        ]), $this->getTestConfig());

        self::assertSame($expected, $sonoff->{$method}($device));
    }

    #[DataProvider('mqttValueMethodProvider')]
    public function testMqttValueReadersReturnEmptyStringForUnknownCommand(string $method): void
    {
        $device = DeviceFactory::fromArray([0, 'socket-1', '192.168.1.8']);
        $sonoff = new Sonoff($this->getTestDeviceRepository(), $this->getClient([
            new Response(200, [], '{"Command":"Unknown"}'),
        ]), $this->getTestConfig());

        self::assertSame('', $sonoff->{$method}($device));
    }

    #[DataProvider('mqttValueMethodProvider')]
    public function testMqttValueReadersReturnEmptyStringForErrorResponse(string $method): void
    {
        $device = DeviceFactory::fromArray([0, 'socket-1', '192.168.1.8']);
        $sonoff = new Sonoff($this->getTestDeviceRepository(), $this->getClient([
            new Response(200, [], '{"ERROR":"request failed"}'),
        ]), $this->getTestConfig());

        self::assertSame('', $sonoff->{$method}($device));
    }

    public static function mqttValueMethodProvider(): array
    {
        return [
            ['getFullTopic', 'FullTopic', '%prefix%/%topic%/'],
            ['getSwitchTopic', 'SwitchTopic', 'switch-topic'],
            ['getMqttRetry', 'MqttRetry', '10'],
            ['getTelePeriod', 'TelePeriod', '300'],
            ['getSensorRetain', 'SensorRetain', 'ON'],
            ['getMqttFingerprint', 'MqttFingerprint', 'AB:CD'],
        ];
    }

    public function testGetMqttFingerprintReturnsEmptyStringWhenFieldIsMissing(): void
    {
        $device = DeviceFactory::fromArray([0, 'socket-1', '192.168.1.8']);
        $sonoff = new Sonoff($this->getTestDeviceRepository(), $this->getClient([
            new Response(200, [], '{"SomeOtherField":"value"}'),
        ]), $this->getTestConfig());

        self::assertSame('', $sonoff->getMqttFingerprint($device));
    }

    public function testDoAjaxReturnsErrorWhenDeviceDoesNotExist(): void
    {
        $repository = $this->createMock(DeviceRepository::class);
        $repository->expects(self::once())
            ->method('getDeviceById')
            ->with(99)
            ->willReturn(null)
        ;

        $sonoff = new Sonoff($repository, $this->getClient(), $this->getTestConfig());
        $result = $sonoff->doAjax(99, 'Power On');

        self::assertSame('No devices found with ID: 99', $result->ERROR);
    }

    public function testDoAjaxReturnsParsedResponse(): void
    {
        $device = DeviceFactory::fromArray([1, 'socket-1', '192.168.1.8']);
        $repository = $this->createMock(DeviceRepository::class);
        $repository->expects(self::once())
            ->method('getDeviceById')
            ->with(1)
            ->willReturn($device)
        ;

        $client = $this->createMock(Client::class);
        $client->expects(self::once())
            ->method('request')
            ->with('GET', 'http://192.168.1.8:80/cm?cmnd=Power+On')
            ->willReturn(new Response(200, [], '{"POWER":"ON"}'))
        ;

        $sonoff = new Sonoff($repository, $client, $this->getTestConfig());
        $result = $sonoff->doAjax(1, 'Power On');

        self::assertSame('ON', $result->POWER);
    }

    public function testDoAjaxReturnsClientExceptionMessage(): void
    {
        $device = DeviceFactory::fromArray([1, 'socket-1', '192.168.1.8']);
        $repository = $this->createMock(DeviceRepository::class);
        $repository->expects(self::once())
            ->method('getDeviceById')
            ->with(1)
            ->willReturn($device)
        ;

        $request = new Request('GET', 'http://192.168.1.8:80/cm?cmnd=Power+On');
        $client = $this->createMock(Client::class);
        $client->expects(self::once())
            ->method('request')
            ->with('GET', 'http://192.168.1.8:80/cm?cmnd=Power+On')
            ->willThrowException(new RequestException('boom', $request))
        ;

        $sonoff = new Sonoff($repository, $client, $this->getTestConfig());
        $result = $sonoff->doAjax(1, 'Power On');

        self::assertSame('boom', $result->ERROR);
    }

    public function testSetDeviceValueDelegatesToRepository(): void
    {
        $device = DeviceFactory::fromArray([1, 'socket-1', '192.168.1.8']);
        $repository = $this->createMock(DeviceRepository::class);
        $repository->expects(self::once())
            ->method('setDeviceValue')
            ->with(1, 'img', 'new-image')
            ->willReturn($device)
        ;

        $sonoff = new Sonoff($repository, $this->getClient(), $this->getTestConfig());

        self::assertSame($device, $sonoff->setDeviceValue(1, 'img', 'new-image'));
    }

    public function testGetPrefixeMixesValuesAndUnknownCommands(): void
    {
        $device = DeviceFactory::fromArray([1, 'socket-1', '192.168.1.8']);
        $sonoff = new Sonoff($this->getTestDeviceRepository(), $this->getClient([
            new Response(200, [], '{"Prefix1":"cmnd"}'),
            new Response(200, [], '{"Command":"Unknown"}'),
            new Response(200, [], '{"Prefix3":"tele"}'),
        ]), $this->getTestConfig());

        $result = $sonoff->getPrefixe($device);

        self::assertSame('cmnd', $result->Prefix1);
        self::assertSame('', $result->Prefix2);
        self::assertSame('tele', $result->Prefix3);
    }

    public function testGetPrefixeReturnsEmptyValueForErrorResponses(): void
    {
        $device = DeviceFactory::fromArray([1, 'socket-1', '192.168.1.8']);
        $sonoff = new Sonoff($this->getTestDeviceRepository(), $this->getClient([
            new Response(200, [], '{"ERROR":"request failed"}'),
            new Response(200, [], '{"Prefix2":"stat"}'),
            new Response(200, [], '{"Prefix3":"tele"}'),
        ]), $this->getTestConfig());

        $result = $sonoff->getPrefixe($device);

        self::assertSame('', $result->Prefix1);
        self::assertSame('stat', $result->Prefix2);
        self::assertSame('tele', $result->Prefix3);
    }

    public function testGetStateTextsMixesValuesAndUnknownCommands(): void
    {
        $device = DeviceFactory::fromArray([1, 'socket-1', '192.168.1.8']);
        $sonoff = new Sonoff($this->getTestDeviceRepository(), $this->getClient([
            new Response(200, [], '{"StateText1":"OFF"}'),
            new Response(200, [], '{"StateText2":"ON"}'),
            new Response(200, [], '{"Command":"Unknown"}'),
            new Response(200, [], '{"StateText4":"TOGGLE"}'),
        ]), $this->getTestConfig());

        $result = $sonoff->getStateTexts($device);

        self::assertSame('OFF', $result->StateText1);
        self::assertSame('ON', $result->StateText2);
        self::assertSame('', $result->StateText3);
        self::assertSame('TOGGLE', $result->StateText4);
    }

    public function testGetStateTextsReturnsEmptyValueForErrorResponses(): void
    {
        $device = DeviceFactory::fromArray([1, 'socket-1', '192.168.1.8']);
        $sonoff = new Sonoff($this->getTestDeviceRepository(), $this->getClient([
            new Response(200, [], '{"ERROR":"request failed"}'),
            new Response(200, [], '{"StateText2":"ON"}'),
            new Response(200, [], '{"StateText3":"TOGGLE"}'),
            new Response(200, [], '{"StateText4":"HOLD"}'),
        ]), $this->getTestConfig());

        $result = $sonoff->getStateTexts($device);

        self::assertSame('', $result->StateText1);
        self::assertSame('ON', $result->StateText2);
        self::assertSame('TOGGLE', $result->StateText3);
        self::assertSame('HOLD', $result->StateText4);
    }

    public function testDoAjaxAllReturnsParsedResultsByDeviceId(): void
    {
        $repository = $this->createMock(DeviceRepository::class);
        $repository->expects(self::once())
            ->method('getDevices')
            ->willReturn([
                new Device(2, ['socket-2'], '192.168.1.2', 'user', 'pass', 'img', 2, false, false, false, []),
                new Device(1, ['socket-1'], '192.168.1.1', 'user', 'pass', 'img', 1, false, false, false, []),
            ])
        ;

        $sonoff = new Sonoff($repository, $this->getClient([
            new Response(200, [], '{"Status":{"DeviceName":"socket-1"}}'),
            new Response(200, [], '{"Status":{"DeviceName":"socket-2"}}'),
        ]), $this->getTestConfig());

        $result = $sonoff->doAjaxAll();

        self::assertSame('socket-1', $result[1]->Status->DeviceName);
        self::assertSame('socket-2', $result[2]->Status->DeviceName);
    }

    public function testDoAjaxAllReturnsEmptyArrayWhenThereAreNoDevices(): void
    {
        $repository = $this->createMock(DeviceRepository::class);
        $repository->expects(self::once())
            ->method('getDevices')
            ->willReturn([])
        ;

        $sonoff = new Sonoff($repository, $this->getClient(), $this->getTestConfig());

        self::assertSame([], $sonoff->doAjaxAll());
    }

    public function testGetFullTopicRetriesAfterWarningAndReturnsSecondAttempt(): void
    {
        $device = DeviceFactory::fromArray([1, 'socket-1', '192.168.1.8']);
        $transactions = [];
        $history = Middleware::history($transactions);
        $mock = new MockHandler([
            new Response(200, [], '{"WARNING":"Enable weblog 2 if response expected"}'),
            new Response(200, [], '{"WebLog":2}'),
            new Response(200, [], '{"FullTopic":"%prefix%/%topic%/"}'),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $client = new Client(['handler' => $handlerStack]);

        $sonoff = new Sonoff($this->getTestDeviceRepository(), $client, $this->getTestConfig());

        self::assertSame('%prefix%/%topic%/', $sonoff->getFullTopic($device));
        self::assertCount(3, $transactions);
        self::assertSame('http://192.168.1.8/cm?cmnd=FullTopic', (string) $transactions[0]['request']->getUri());
        self::assertSame('http://192.168.1.8/cm?cmnd=Weblog+2', (string) $transactions[1]['request']->getUri());
        self::assertSame('http://192.168.1.8/cm?cmnd=FullTopic', (string) $transactions[2]['request']->getUri());
    }

    public function testGetFullTopicReturnsOriginalWarningWhenWeblogRetryStillWarns(): void
    {
        $device = DeviceFactory::fromArray([1, 'socket-1', '192.168.1.8']);
        $transactions = [];
        $history = Middleware::history($transactions);
        $mock = new MockHandler([
            new Response(200, [], '{"WARNING":"Enable weblog 2 if response expected"}'),
            new Response(200, [], '{"WARNING":"still noisy"}'),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $client = new Client(['handler' => $handlerStack]);

        $sonoff = new Sonoff($this->getTestDeviceRepository(), $client, $this->getTestConfig());
        $result = $sonoff->getFullTopic($device);

        self::assertSame('', $result);
        self::assertCount(2, $transactions);
        self::assertSame('http://192.168.1.8/cm?cmnd=FullTopic', (string) $transactions[0]['request']->getUri());
        self::assertSame('http://192.168.1.8/cm?cmnd=Weblog+2', (string) $transactions[1]['request']->getUri());
    }

    #[DataProvider('mqttWarningMethodProvider')]
    public function testMqttValueReadersReturnEmptyStringWhenWarningPersists(string $method): void
    {
        $device = DeviceFactory::fromArray([1, 'socket-1', '192.168.1.8']);
        $transactions = [];
        $history = Middleware::history($transactions);
        $mock = new MockHandler([
            new Response(200, [], '{"WARNING":"Enable weblog 2 if response expected"}'),
            new Response(200, [], '{"WARNING":"still noisy"}'),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $client = new Client(['handler' => $handlerStack]);

        $sonoff = new Sonoff($this->getTestDeviceRepository(), $client, $this->getTestConfig());

        self::assertSame('', $sonoff->{$method}($device));
        self::assertCount(2, $transactions);
    }

    public static function mqttWarningMethodProvider(): array
    {
        return [
            ['getSwitchTopic'],
            ['getMqttRetry'],
            ['getTelePeriod'],
            ['getSensorRetain'],
            ['getMqttFingerprint'],
        ];
    }

    public function testSaveConfigSkipsWarningRetryForBacklogCommands(): void
    {
        $device = DeviceFactory::fromArray([1, 'socket-1', '192.168.1.8']);
        $transactions = [];
        $history = Middleware::history($transactions);
        $mock = new MockHandler([
            new Response(200, [], '{"WARNING":"Enable weblog 2 if response expected"}'),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $client = new Client(['handler' => $handlerStack]);

        $sonoff = new Sonoff($this->getTestDeviceRepository(), $client, $this->getTestConfig());
        $result = $sonoff->saveConfig($device, 'Backlog Template {"NAME":"Demo"}');

        self::assertSame('Enable weblog 2 if response expected', $result->WARNING);
        self::assertCount(1, $transactions);
        self::assertSame(
            'http://192.168.1.8/cm?cmnd=Backlog+Template+%7B%22NAME%22%3A%22Demo%22%7D',
            (string) $transactions[0]['request']->getUri()
        );
    }

    public function testGetTimersConfigLoadsGlobalToggleAndAllTimers(): void
    {
        $device = DeviceFactory::fromArray([1, 'socket-1', '192.168.1.8']);
        $responses = [new Response(200, [], '{"Timers":"ON"}')];

        foreach (range(1, 16) as $index) {
            $responses[] = new Response(200, [], json_encode([
                'Timer'.$index => [
                    'Enable' => 1 === $index ? 1 : 0,
                    'Mode' => 1 === $index ? 2 : 0,
                    'Time' => 1 === $index ? '06:30' : '00:00',
                    'Window' => 1 === $index ? 15 : 0,
                    'Days' => 1 === $index ? '--TWT--' : '-------',
                    'Repeat' => 1 === $index ? 1 : 0,
                    'Output' => 1 === $index ? 2 : 1,
                    'Action' => 1 === $index ? 3 : 0,
                ],
            ], JSON_THROW_ON_ERROR));
        }

        $sonoff = new Sonoff($this->getTestDeviceRepository(), $this->getClient($responses), $this->getTestConfig());
        $result = $sonoff->getTimersConfig($device);

        self::assertInstanceOf(\stdClass::class, $result);
        self::assertSame(1, $result->enabled);
        self::assertCount(16, $result->timers);
        self::assertSame('06:30', $result->timers[1]->Time);
        self::assertSame('--TWT--', $result->timers[1]->Days);
        self::assertSame(0, $result->timers[16]->Enable);
    }

    public function testGetTimersConfigReturnsNullWhenTimersAreUnsupported(): void
    {
        $device = DeviceFactory::fromArray([1, 'socket-1', '192.168.1.8']);
        $sonoff = new Sonoff($this->getTestDeviceRepository(), $this->getClient([
            new Response(200, [], '{"Command":"Unknown"}'),
        ]), $this->getTestConfig());

        self::assertNull($sonoff->getTimersConfig($device));
    }

    public function testSaveTimersSendsGlobalToggleAndIndividualTimerCommands(): void
    {
        $device = DeviceFactory::fromArray([1, 'socket-1', '192.168.1.8']);
        $transactions = [];
        $history = Middleware::history($transactions);
        $responses = [];

        foreach (range(1, 17) as $index) {
            $responses[] = new Response(200, [], '{"Done":"true"}');
        }

        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $client = new Client(['handler' => $handlerStack]);

        $sonoff = new Sonoff($this->getTestDeviceRepository(), $client, $this->getTestConfig());
        $result = $sonoff->saveTimers($device, $this->getTimerSettingsPayload());

        self::assertInstanceOf(\stdClass::class, $result);
        self::assertCount(17, $transactions);
        self::assertSame('http://192.168.1.8/cm?cmnd=Timers+1', (string) $transactions[0]['request']->getUri());
        self::assertSame(
            'http://192.168.1.8/cm?cmnd=Timer1+%7B%22Enable%22%3A1%2C%22Mode%22%3A2%2C%22Time%22%3A%2206%3A30%22%2C%22Window%22%3A15%2C%22Days%22%3A%22--TWT--%22%2C%22Repeat%22%3A1%2C%22Output%22%3A2%2C%22Action%22%3A3%7D',
            (string) $transactions[1]['request']->getUri()
        );
        self::assertStringContainsString('Timer16+', (string) $transactions[16]['request']->getUri());
    }

    public function testGetTimerSummariesMarksOnlyRelaysWithEnabledTimers(): void
    {
        $deviceRepository = $this->createMock(DeviceRepository::class);
        $deviceRepository->method('getDevices')->willReturn([
            new Device(1, ['desk', 'lamp'], '192.168.1.8', '', '', position: 1),
            new Device(2, ['sensor'], '192.168.1.9', '', '', position: 2),
        ]);

        $responses = [
            new Response(200, [], '{"Timers":"ON"}'),
            new Response(200, [], '{"Timer1":{"Enable":1,"Mode":0,"Time":"06:30","Window":0,"Days":"SMTWTFS","Repeat":1,"Output":2,"Action":1}}'),
        ];

        foreach (range(2, 16) as $index) {
            $responses[] = new Response(200, [], json_encode([
                'Timer'.$index => [
                    'Enable' => 0,
                    'Mode' => 0,
                    'Time' => '00:00',
                    'Window' => 0,
                    'Days' => '-------',
                    'Repeat' => 0,
                    'Output' => 1,
                    'Action' => 0,
                ],
            ]));
        }

        $responses[] = new Response(200, [], '{"Command":"Unknown"}');

        $sonoff = new Sonoff($deviceRepository, $this->getClient($responses), $this->getTestConfig());
        $summaries = $sonoff->getTimerSummaries();

        self::assertTrue($summaries[1]['supported']);
        self::assertTrue($summaries[1]['hasActiveTimer']);
        self::assertFalse($summaries[1]['relays'][1]['hasActiveTimer']);
        self::assertTrue($summaries[1]['relays'][2]['hasActiveTimer']);
        self::assertFalse($summaries[2]['supported']);
        self::assertFalse($summaries[2]['hasActiveTimer']);
    }

    public function testSearchReturnsEmptyArrayWhenNoUrlsAreProvided(): void
    {
        $sonoff = new Sonoff($this->getTestDeviceRepository(), $this->getClient(), $this->getTestConfig());

        self::assertSame([], $sonoff->search([]));
    }

    private function getClient(array $responses = []): Client
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);

        return new Client(['handler' => $handlerStack]);
    }

    private function getTestDeviceRepository(): DeviceRepository
    {
        $deviceFile = $this->root->url().'/devices.csv';
        touch($deviceFile);

        $tmpDir = $this->root->url().'/tmp/';
        mkdir($tmpDir);

        return new DeviceRepository($deviceFile, $tmpDir, $this->getCipher());
    }

    private function getCipher(): DevicePasswordCipher
    {
        return new DevicePasswordCipher(new DevicePasswordKeyProvider($this->getDataDir()));
    }

    private function getTestConfig(): Config
    {
        return new Config($this->root->url().'/', $this->root->url().'/');
    }

    private function getBackupDir(): string
    {
        $backupDir = $this->root->url().'/backups/';
        mkdir($backupDir);

        return $backupDir;
    }

    private function getDataDir(): string
    {
        $dataDir = $this->root->url().'/data/';
        if (!is_dir($dataDir)) {
            mkdir($dataDir);
        }

        return $dataDir;
    }

    private function getTimerSettingsPayload(): array
    {
        $settings = ['Timers' => '1'];

        foreach (range(1, 16) as $index) {
            $settings['Timer'.$index] = [
                'Enable' => 1 === $index ? '1' : '0',
                'Mode' => 1 === $index ? '2' : '0',
                'Time' => 1 === $index ? '06:30' : '00:00',
                'Window' => 1 === $index ? '15' : '0',
                'Days' => 1 === $index ? '--TWT--' : '-------',
                'Repeat' => 1 === $index ? '1' : '0',
                'Output' => 1 === $index ? '2' : '1',
                'Action' => 1 === $index ? '3' : '0',
            ];
        }

        return $settings;
    }
}
