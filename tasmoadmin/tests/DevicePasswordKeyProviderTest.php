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

    public function testUsesMatchingEnvironmentAndSidecarKey(): void
    {
        $encodedKey = base64_encode(random_bytes(DevicePasswordKeyProvider::KEY_LENGTH));
        file_put_contents($this->getDataDir().DevicePasswordKeyProvider::SIDECAR_FILENAME, $encodedKey);
        putenv(DevicePasswordKeyProvider::ENV_NAME.'='.$encodedKey);

        $provider = new DevicePasswordKeyProvider($this->getDataDir());

        self::assertSame(base64_decode($encodedKey, true), $provider->getKey());
    }

    public function testRejectsInvalidSidecarKey(): void
    {
        file_put_contents($this->getDataDir().DevicePasswordKeyProvider::SIDECAR_FILENAME, 'invalid');

        $provider = new DevicePasswordKeyProvider($this->getDataDir());

        $this->expectException(DeviceCredentialException::class);
        $provider->getKey();
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

    public function testCachesResolvedKeyAcrossSubsequentReads(): void
    {
        $firstEncodedKey = base64_encode(random_bytes(DevicePasswordKeyProvider::KEY_LENGTH));
        $secondEncodedKey = base64_encode(random_bytes(DevicePasswordKeyProvider::KEY_LENGTH));
        putenv(DevicePasswordKeyProvider::ENV_NAME.'='.$firstEncodedKey);

        $provider = new DevicePasswordKeyProvider($this->getDataDir());
        $firstKey = $provider->getKey();

        putenv(DevicePasswordKeyProvider::ENV_NAME.'='.$secondEncodedKey);

        self::assertSame($firstKey, $provider->getKey());
    }

    public function testGetKeyFilePathNormalizesTrailingSlash(): void
    {
        $provider = new DevicePasswordKeyProvider($this->root->url().'/data');

        self::assertSame(
            $this->root->url().'/data/'.DevicePasswordKeyProvider::SIDECAR_FILENAME,
            $provider->getKeyFilePath()
        );
    }

    public function testFailsWhenGeneratedKeyCannotBePersisted(): void
    {
        $tempDir = sys_get_temp_dir().'/device-key-provider-'.bin2hex(random_bytes(8));
        mkdir($tempDir);
        chmod($tempDir, 0o500);

        set_error_handler(static fn () => true);

        try {
            $provider = new DevicePasswordKeyProvider($tempDir);

            $this->expectException(DeviceCredentialException::class);
            $provider->getKey();
        } finally {
            restore_error_handler();
            chmod($tempDir, 0o700);
            rmdir($tempDir);
        }
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
