<?php

namespace Tests\TasmoAdmin;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use TasmoAdmin\DeviceCredentialException;
use TasmoAdmin\DevicePasswordKeyProvider;

class DevicePasswordKeyProviderTest extends TestCase
{
    private vfsStreamDirectory $root;

    protected function setUp(): void
    {
        $this->root = vfsStream::setup('config');
    }

    protected function tearDown(): void
    {
        putenv(DevicePasswordKeyProvider::ENV_NAME);
    }

    public function testUsesValidEnvironmentKey(): void
    {
        $encodedKey = base64_encode(random_bytes(DevicePasswordKeyProvider::KEY_LENGTH));
        putenv(DevicePasswordKeyProvider::ENV_NAME.'='.$encodedKey);

        $provider = new DevicePasswordKeyProvider($this->getDataDir());

        self::assertSame(base64_decode($encodedKey, true), $provider->getKey());
    }

    public function testRejectsInvalidEnvironmentKey(): void
    {
        putenv(DevicePasswordKeyProvider::ENV_NAME.'=invalid');

        $provider = new DevicePasswordKeyProvider($this->getDataDir());

        $this->expectException(DeviceCredentialException::class);
        $provider->getKey();
    }

    public function testReusesSidecarKey(): void
    {
        $encodedKey = base64_encode(random_bytes(DevicePasswordKeyProvider::KEY_LENGTH));
        file_put_contents($this->getDataDir().DevicePasswordKeyProvider::SIDECAR_FILENAME, $encodedKey);

        $provider = new DevicePasswordKeyProvider($this->getDataDir());

        self::assertSame(base64_decode($encodedKey, true), $provider->getKey());
    }

    public function testGeneratesSidecarKeyWhenNoSourceExists(): void
    {
        $provider = new DevicePasswordKeyProvider($this->getDataDir());

        $key = $provider->getKey();

        self::assertSame(DevicePasswordKeyProvider::KEY_LENGTH, strlen($key));
        self::assertFileExists($provider->getKeyFilePath());
        self::assertSame($key, base64_decode((string) file_get_contents($provider->getKeyFilePath()), true));
    }

    public function testFailsWhenEnvironmentAndSidecarMismatch(): void
    {
        file_put_contents(
            $this->getDataDir().DevicePasswordKeyProvider::SIDECAR_FILENAME,
            base64_encode(random_bytes(DevicePasswordKeyProvider::KEY_LENGTH))
        );
        putenv(DevicePasswordKeyProvider::ENV_NAME.'='.base64_encode(random_bytes(DevicePasswordKeyProvider::KEY_LENGTH)));

        $provider = new DevicePasswordKeyProvider($this->getDataDir());

        $this->expectException(DeviceCredentialException::class);
        $provider->getKey();
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
