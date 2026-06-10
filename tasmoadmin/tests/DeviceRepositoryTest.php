<?php

namespace Tests\TasmoAdmin;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use TasmoAdmin\DeviceCredentialException;
use TasmoAdmin\DevicePasswordCipher;
use TasmoAdmin\DevicePasswordKeyProvider;
use TasmoAdmin\DeviceRepository;

class DeviceRepositoryTest extends TestCase
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

    public function testConstructorCreateFile(): void
    {
        $repo = $this->getVirtualRepo(false);
        self::assertEquals([], $repo->getDevices());
    }

    public function testAddDevice(): void
    {
        $repo = $this->getVirtualRepo();

        $request = [
            'device_name' => ['socket-1'],
            'device_ip' => '127.0.0.1',
            'device_img' => 'orange',
            'device_position' => 1,
        ];

        $repo->addDevice($request);
        self::assertCount(1, $repo->getDevices());
        $device = $repo->getDevices()[0];
        self::assertEquals(['socket-1'], $device->names);
        self::assertEquals(['socket-1'], $device->friendlyNames);
    }

    public function testAddDeviceWithEscapeCharacter(): void
    {
        $repo = $this->getVirtualRepo();

        $request = [
            'device_name' => ['socket-\\\1'],
            'device_ip' => '127.0.0.1',
            'device_img' => 'orange',
            'device_position' => 1,
        ];

        $repo->addDevice($request);

        self::assertCount(1, $repo->getDevices());
        $device = $repo->getDevices()[0];

        self::assertEquals('socket-\\\1', $device->names[0]);
    }

    public function testAddDeviceId(): void
    {
        $repo = $this->getVirtualRepo();

        $repo->addDevice([
            'device_name' => ['socket-1'],
            'device_ip' => '127.0.0.1',
            'device_img' => 'orange',
            'device_position' => 1,
        ]);
        $repo->addDevice([
            'device_name' => ['socket-2'],
            'device_ip' => '127.0.0.1',
            'device_img' => 'orange',
            'device_position' => 2,
        ]);
        self::assertCount(2, $repo->getDevices());
        $repo->removeDevice(1);
        self::assertCount(1, $repo->getDevices());
        $repo->addDevice([
            'device_name' => ['socket-3'],
            'device_ip' => '127.0.0.1',
            'device_img' => 'orange',
            'device_position' => 2,
        ]);
        $device1 = $repo->getDevices()[0];
        self::assertEquals(['socket-2'], $device1->names);
        self::assertEquals(2, $device1->id);
        $device2 = $repo->getDevices()[1];
        self::assertEquals(['socket-3'], $device2->names);
        self::assertEquals(3, $device2->id);
    }

    public function testAddDevicesEmptyDevices(): void
    {
        $repo = $this->getVirtualRepo();
        $repo->addDevices([], 'user', 'pass');
        self::assertCount(0, $repo->getDevices());
    }

    public function testAddDevicesDevices(): void
    {
        $repo = $this->getVirtualRepo();
        $devices = [
            [
                'device_name' => ['socket-1'],
                'device_ip' => '127.0.0.1',
                'device_img' => 'orange',
                'device_position' => 1,
            ],
        ];
        $repo->addDevices($devices, 'user', 'pass');
        self::assertCount(1, $repo->getDevices());
        $device = $repo->getDevices()[0];
        self::assertEquals(['socket-1'], $device->names);
        self::assertEquals(['socket-1'], $device->friendlyNames);
        self::assertTrue($device->isUpdatable);
        self::assertEquals(80, $device->port);
    }

    public function testGetDeviceByIdValid(): void
    {
        $repo = $this->getValidRepo();
        $device = $repo->getDeviceById(1);

        self::assertEquals(1, $device->id);
        self::assertEquals(['socket-1'], $device->names);
        self::assertEquals('192.168.1.2', $device->ip);
        self::assertEquals('user', $device->username);
        self::assertEquals('password', $device->password);
        self::assertEquals('bulb_1', $device->img);
        self::assertEquals(2, $device->position);
        self::assertEquals(5000, $device->port);
        self::assertEquals(['socket-1'], $device->friendlyNames);
    }

    public function testGetDeviceByIdInvalidId(): void
    {
        $repo = $this->getValidRepo();
        self::assertNull($repo->getDeviceById(9));
    }

    public function testGetDeviceByIdIgnoresUnreadableLaterRows(): void
    {
        $deviceFile = $this->root->url().'/devices-get-by-id.csv';
        file_put_contents(
            $deviceFile,
            implode(
                PHP_EOL,
                [
                    '1,socket-1,192.168.1.2,user,password,bulb_1,2',
                    '2,socket-2,192.168.1.3,user,'.DevicePasswordCipher::STORAGE_PREFIX.'%%%,bulb_1,3',
                ]
            ).PHP_EOL
        );

        $repo = $this->createRepository($deviceFile);
        $device = $repo->getDeviceById(1);

        self::assertSame(1, $device->id);
        self::assertSame('password', $device->password);
        self::assertStringContainsString(DevicePasswordCipher::STORAGE_PREFIX, (string) file_get_contents($deviceFile));
    }

    public function testGetDevices(): void
    {
        $devices = $this->getValidRepo()->getDevices();
        self::assertCount(3, $devices);
    }

    public function testGetDevicesRewritesPlaintextFixtureToEncryptedCells(): void
    {
        $deviceFile = $this->copyFixtureToVirtualFile('devices.csv');
        $repo = $this->createRepository($deviceFile);

        $devices = $repo->getDevices();

        self::assertCount(3, $devices);
        self::assertSame('password', $devices[0]->password);
        self::assertStringContainsString(DevicePasswordCipher::STORAGE_PREFIX, (string) file_get_contents($deviceFile));
        self::assertStringNotContainsString(',password,', (string) file_get_contents($deviceFile));
    }

    public function testMixedPlaintextAndEncryptedRowsConvergeToEncryptedStorage(): void
    {
        $cipher = $this->getCipher();
        $deviceFile = $this->root->url().'/devices-mixed.csv';
        file_put_contents(
            $deviceFile,
            implode(
                PHP_EOL,
                [
                    '1,socket-1,192.168.1.2,user,password,bulb_1,2',
                    sprintf('2,socket-2,192.168.1.3,user,%s,bulb_1,3', $cipher->encrypt('password')),
                ]
            ).PHP_EOL
        );

        $repo = $this->createRepository($deviceFile);
        $devices = $repo->getDevices();

        self::assertSame('password', $devices[0]->password);
        self::assertSame('password', $devices[1]->password);
        self::assertSame(2, substr_count((string) file_get_contents($deviceFile), DevicePasswordCipher::STORAGE_PREFIX));
        self::assertStringNotContainsString(',password,', (string) file_get_contents($deviceFile));
    }

    public function testLegacyPlaintextPasswordWithStoragePrefixIsMigrated(): void
    {
        $deviceFile = $this->root->url().'/devices-legacy-prefix.csv';
        file_put_contents(
            $deviceFile,
            '1,socket-1,192.168.1.2,user,enc:v1:plain-password,bulb_1,2'.PHP_EOL
        );

        $repo = $this->createRepository($deviceFile);
        $device = $repo->getDeviceById(1);

        self::assertSame('enc:v1:plain-password', $device->password);
        $storedContents = (string) file_get_contents($deviceFile);
        self::assertStringContainsString(DevicePasswordCipher::STORAGE_PREFIX, $storedContents);
        self::assertStringNotContainsString(',enc:v1:plain-password,', $storedContents);
    }

    public function testGetDevicesEmptyRepo(): void
    {
        $devices = $this->getEmptyRepo()->getDevices();
        self::assertCount(0, $devices);
    }

    public function testSetDeviceValueMissingDevice(): void
    {
        $repo = $this->getEmptyRepo();
        self::assertNull($repo->setDeviceValue(1, 'names', '1'));
    }

    public function testSetDeviceValueInvalidField(): void
    {
        $repo = $this->getValidRepo();
        self::assertNull($repo->setDeviceValue(1, 'random', '1'));
    }

    public function testSetDeviceValueValid(): void
    {
        $repo = $this->getVirtualRepo();
        $repo->addDevices([['device_name' => ['socket-1']]], 'user', 'pass');
        $repo->setDeviceValue(1, 'names', ['socket-2']);
        $device = $repo->getDeviceById(1);
        self::assertEquals(['socket-2'], $device->names);
    }

    public function testSetDeviceValueFriendlyNames(): void
    {
        $repo = $this->getVirtualRepo();
        $repo->addDevices([['device_name' => ['socket-1']]], 'user', 'pass');

        $repo->setDeviceValue(1, 'friendlyNames', ['webui-socket-1']);

        $device = $repo->getDeviceById(1);
        self::assertEquals(['webui-socket-1'], $device->friendlyNames);
    }

    public function testAddDevicesStoresFriendlyNamesSeparately(): void
    {
        $repo = $this->getVirtualRepo();
        $repo->addDevices(
            [[
                'device_name' => ['office-lamp'],
                'device_friendly_name' => ['lamp-webui'],
                'device_ip' => '127.0.0.1',
            ]],
            'user',
            'pass'
        );

        $device = $repo->getDeviceById(1);
        self::assertSame(['office-lamp'], $device->names);
        self::assertSame(['lamp-webui'], $device->friendlyNames);
    }

    public function testSetDeviceValuePasswordNeverWritesPlaintextBackToDisk(): void
    {
        $repo = $this->getVirtualRepo();
        $repo->addDevices([['device_name' => ['socket-1']]], 'user', 'pass');

        $repo->setDeviceValue(1, 'password', 'new-secret');

        $contents = (string) file_get_contents($this->getVirtualDeviceFilePath());
        self::assertStringContainsString(DevicePasswordCipher::STORAGE_PREFIX, $contents);
        self::assertStringNotContainsString(',new-secret,', $contents);
        self::assertSame('new-secret', $repo->getDeviceById(1)->password);
    }

    public function testRemoveDeviceValid(): void
    {
        $repo = $this->getVirtualRepoWithDevices(5);
        $repo->removeDevice(2);
        self::assertNull($repo->getDeviceById(2));
        $devices = $repo->getDevices();
        self::assertCount(4, $devices);
        self::assertEquals(1, $devices[0]->position);
        self::assertEquals(3, $devices[1]->position);
    }

    public function testRemoveDeviceInvalidDeviceId(): void
    {
        $repo = $this->getVirtualRepoWithDevices(5);
        $repo->removeDevice(6);
        self::assertCount(5, $repo->getDevices());
    }

    public function testRemoveDevicesValid(): void
    {
        $repo = $this->getVirtualRepoWithDevices(5);
        $repo->removeDevices([1, 2, 3]);
        self::assertNull($repo->getDeviceById(2));
        $devices = $repo->getDevices();
        self::assertCount(2, $devices);
        self::assertEquals(4, $devices[0]->position);
        self::assertEquals(5, $devices[1]->position);
    }

    public function testRemoveDevicesWrongOrder(): void
    {
        $repo = $this->getVirtualRepoWithDevices(5);
        $repo->removeDevices([3, 2, 1]);
        self::assertNull($repo->getDeviceById(2));
        $devices = $repo->getDevices();
        self::assertCount(2, $devices);
        self::assertEquals(4, $devices[0]->position);
        self::assertEquals(5, $devices[1]->position);
    }

    public function testUpdateDeviceNeverWritesPlaintextBackToDisk(): void
    {
        $repo = $this->getValidRepo();
        $device = $repo->getDeviceById(1);
        $device->password = 'updated-secret';

        $repo->updateDevice($device);

        $contents = (string) file_get_contents($this->getVirtualDeviceFilePath('devices.csv'));
        self::assertStringContainsString(DevicePasswordCipher::STORAGE_PREFIX, $contents);
        self::assertStringNotContainsString(',updated-secret,', $contents);
        self::assertSame('updated-secret', $repo->getDeviceById(1)->password);
    }

    public function testAddDeviceNeverWritesPlaintextBackToDisk(): void
    {
        $repo = $this->getVirtualRepo();
        $repo->addDevices(
            [['device_name' => ['socket-1'], 'device_ip' => '127.0.0.1']],
            'user',
            'secret'
        );

        $contents = (string) file_get_contents($this->getVirtualDeviceFilePath());
        self::assertStringContainsString(DevicePasswordCipher::STORAGE_PREFIX, $contents);
        self::assertStringNotContainsString(',secret,', $contents);
        self::assertSame('secret', $repo->getDeviceById(1)->password);
    }

    public function testDecryptFailureThrowsDeviceCredentialException(): void
    {
        putenv(DevicePasswordKeyProvider::ENV_NAME.'='.base64_encode(random_bytes(DevicePasswordKeyProvider::KEY_LENGTH)));
        $cipher = $this->getCipher();
        $deviceFile = $this->root->url().'/devices-bad-key.csv';
        file_put_contents(
            $deviceFile,
            sprintf('1,socket-1,192.168.1.2,user,%s,bulb_1,2', $cipher->encrypt('password')).PHP_EOL
        );

        putenv(DevicePasswordKeyProvider::ENV_NAME.'='.base64_encode(random_bytes(DevicePasswordKeyProvider::KEY_LENGTH)));
        $repo = $this->createRepository($deviceFile);

        $this->expectException(DeviceCredentialException::class);
        $repo->getDevices();
    }

    public function testGetDevicesByIds(): void
    {
        $repo = $this->getVirtualRepoWithDevices(5);
        $devices = $repo->getDevicesByIds([1, 5]);
        self::assertEquals(1, $devices[0]->id);
        self::assertEquals(5, $devices[1]->id);
    }

    private function getVirtualRepoWithDevices(int $count): DeviceRepository
    {
        $repo = $this->getVirtualRepo();
        $devices = [];
        for ($i = 1; $i <= $count; ++$i) {
            $devices[] = [
                'device_name' => [sprintf('socket-%d', $i)],
                'device_ip' => sprintf('127.0.0.%d', $i),
                'device_img' => 'orange',
                'device_position' => $i,
            ];
        }

        $repo->addDevices($devices, 'user', 'pass');

        return $repo;
    }

    private function getVirtualRepo(bool $withFile = true): DeviceRepository
    {
        $deviceFile = $this->getVirtualDeviceFilePath();
        if ($withFile && !file_exists($deviceFile)) {
            touch($deviceFile);
        }

        return $this->createRepository($deviceFile);
    }

    private function getValidRepo(): DeviceRepository
    {
        return $this->createRepository($this->copyFixtureToVirtualFile('devices.csv'));
    }

    private function getEmptyRepo(): DeviceRepository
    {
        return $this->createRepository($this->copyFixtureToVirtualFile('empty_devices.csv'));
    }

    private function createRepository(string $deviceFile): DeviceRepository
    {
        return new DeviceRepository($deviceFile, $this->getTmpDir(), $this->getCipher());
    }

    private function getCipher(): DevicePasswordCipher
    {
        return new DevicePasswordCipher(new DevicePasswordKeyProvider($this->getDataDir()));
    }

    private function copyFixtureToVirtualFile(string $fixtureName): string
    {
        $path = $this->getVirtualDeviceFilePath($fixtureName);
        file_put_contents($path, TestUtils::loadFixture($fixtureName));

        return $path;
    }

    private function getVirtualDeviceFilePath(string $fileName = 'devices.csv'): string
    {
        return $this->root->url().'/'.$fileName;
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
}
