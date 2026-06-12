<?php

namespace Tests\TasmoAdmin;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use TasmoAdmin\DeviceCredentialException;
use TasmoAdmin\DevicePasswordCipher;
use TasmoAdmin\DevicePasswordKeyProvider;

class DevicePasswordCipherTest extends TestCase
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

    public function testEncryptDecryptRoundTrip(): void
    {
        $cipher = $this->getCipher();

        $encryptedPassword = $cipher->encrypt('secret-password');

        self::assertStringStartsWith(DevicePasswordCipher::STORAGE_PREFIX, $encryptedPassword);
        self::assertSame('secret-password', $cipher->decrypt($encryptedPassword));
    }

    public function testEmptyPasswordPassthrough(): void
    {
        $cipher = $this->getCipher();

        self::assertSame('', $cipher->encrypt(''));
        self::assertSame('', $cipher->decrypt(''));
    }

    public function testEncryptsPasswordsThatStartWithStoragePrefix(): void
    {
        $cipher = $this->getCipher();

        $encryptedPassword = $cipher->encrypt(DevicePasswordCipher::STORAGE_PREFIX.'plain-password');

        self::assertNotSame(DevicePasswordCipher::STORAGE_PREFIX.'plain-password', $encryptedPassword);
        self::assertSame(
            DevicePasswordCipher::STORAGE_PREFIX.'plain-password',
            $cipher->decrypt($encryptedPassword)
        );
    }

    public function testRejectsMalformedPayload(): void
    {
        $cipher = $this->getCipher();

        $this->expectException(DeviceCredentialException::class);
        $cipher->decrypt(DevicePasswordCipher::STORAGE_PREFIX.'%%%');
    }

    public function testRejectsPayloadEncryptedWithWrongKey(): void
    {
        $cipherA = $this->getCipher(base64_encode(random_bytes(DevicePasswordKeyProvider::KEY_LENGTH)));
        $encryptedPassword = $cipherA->encrypt('secret-password');

        $cipherB = $this->getCipher(base64_encode(random_bytes(DevicePasswordKeyProvider::KEY_LENGTH)));

        $this->expectException(DeviceCredentialException::class);
        $cipherB->decrypt($encryptedPassword);
    }

    public function testRecognizesEncryptedPayloadShape(): void
    {
        $cipher = $this->getCipher();

        self::assertTrue($cipher->isRecognizedEncryptedPayload($cipher->encrypt('secret-password')));
        self::assertFalse($cipher->isRecognizedEncryptedPayload(DevicePasswordCipher::STORAGE_PREFIX.'plain-password'));
    }

    public function testIsEncryptedOnlyChecksStoragePrefix(): void
    {
        $cipher = $this->getCipher();

        self::assertTrue($cipher->isEncrypted(DevicePasswordCipher::STORAGE_PREFIX.'anything'));
        self::assertFalse($cipher->isEncrypted('plain-password'));
    }

    public function testRejectsTruncatedCiphertextPayload(): void
    {
        $cipher = $this->getCipher();

        $this->expectException(DeviceCredentialException::class);
        $cipher->decrypt(DevicePasswordCipher::STORAGE_PREFIX.base64_encode('short'));
    }

    private function getCipher(?string $encodedKey = null): DevicePasswordCipher
    {
        $encodedKey ??= base64_encode(random_bytes(DevicePasswordKeyProvider::KEY_LENGTH));
        putenv(DevicePasswordKeyProvider::ENV_NAME.'='.$encodedKey);

        return new DevicePasswordCipher(new DevicePasswordKeyProvider($this->getDataDir()));
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
