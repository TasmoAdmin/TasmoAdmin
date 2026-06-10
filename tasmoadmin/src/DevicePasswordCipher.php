<?php

namespace TasmoAdmin;

class DevicePasswordCipher
{
    public const STORAGE_PREFIX = 'enc:v1:';

    private const CIPHER = 'aes-256-gcm';
    private const TAG_LENGTH = 16;

    private DevicePasswordKeyProvider $keyProvider;

    private int $ivLength;

    public function __construct(DevicePasswordKeyProvider $keyProvider)
    {
        $this->keyProvider = $keyProvider;
        $this->ivLength = openssl_cipher_iv_length(self::CIPHER);
    }

    public function encrypt(string $password): string
    {
        if ('' === $password) {
            return '';
        }

        $iv = random_bytes($this->ivLength);
        $tag = '';
        $ciphertext = openssl_encrypt(
            $password,
            self::CIPHER,
            $this->keyProvider->getKey(),
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            self::TAG_LENGTH
        );

        if (false === $ciphertext) {
            throw new DeviceCredentialException('Failed to encrypt the stored device password.');
        }

        return self::STORAGE_PREFIX.base64_encode($iv.$tag.$ciphertext);
    }

    public function decrypt(string $password): string
    {
        if ('' === $password || !$this->isEncrypted($password)) {
            return $password;
        }

        $payload = substr($password, strlen(self::STORAGE_PREFIX));
        $decodedPayload = base64_decode($payload, true);
        if (false === $decodedPayload) {
            throw new DeviceCredentialException('Stored device password is not valid base64 ciphertext.');
        }

        if (strlen($decodedPayload) <= $this->ivLength + self::TAG_LENGTH) {
            throw new DeviceCredentialException('Stored device password ciphertext is truncated.');
        }

        $iv = substr($decodedPayload, 0, $this->ivLength);
        $tag = substr($decodedPayload, $this->ivLength, self::TAG_LENGTH);
        $ciphertext = substr($decodedPayload, $this->ivLength + self::TAG_LENGTH);

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $this->keyProvider->getKey(),
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if (false === $plaintext) {
            throw new DeviceCredentialException('Stored device password could not be decrypted with the configured key.');
        }

        return $plaintext;
    }

    public function isEncrypted(string $password): bool
    {
        return str_starts_with($password, self::STORAGE_PREFIX);
    }

    public function isRecognizedEncryptedPayload(string $password): bool
    {
        if (!$this->isEncrypted($password)) {
            return false;
        }

        $payload = substr($password, strlen(self::STORAGE_PREFIX));
        $decodedPayload = base64_decode($payload, true);
        if (false === $decodedPayload) {
            return false;
        }

        return strlen($decodedPayload) > $this->ivLength + self::TAG_LENGTH;
    }
}
