<?php

declare(strict_types=1);

namespace TasmoAdmin\Helper;

use lbuchs\WebAuthn\WebAuthn;
use lbuchs\WebAuthn\WebAuthnException;
use TasmoAdmin\PasskeyRepository;

/**
 * Runs the WebAuthn registration and login ceremonies for passkeys.
 * The pending challenge is kept in the PHP session between the two requests.
 */
class PasskeyHelper
{
    private const SESSION_KEY = 'passkey_challenge';
    private const CHALLENGE_TTL = 120;
    private const TIMEOUT = 60;

    private WebAuthn $webAuthn;

    private PasskeyRepository $repository;

    public function __construct(PasskeyRepository $repository, string $rpId, string $rpName = 'TasmoAdmin')
    {
        $this->repository = $repository;
        $this->webAuthn = new WebAuthn($rpName, $rpId, ['none'], true);
    }

    /**
     * @param array<string, mixed> $session
     */
    public function registrationOptions(string $username, array &$session): object
    {
        $username = '' === $username ? 'admin' : $username;
        $userId = substr(hash('sha256', 'tasmoadmin:'.$username, true), 0, 16);
        $exclude = array_map(
            static fn (array $entry): string => self::base64UrlDecode($entry['id']),
            $this->repository->all()
        );

        $args = $this->webAuthn->getCreateArgs($userId, $username, $username, self::TIMEOUT, true, true, null, $exclude);
        $this->storeChallenge('register', $session);

        return $args;
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $session
     *
     * @throws WebAuthnException
     */
    public function finishRegistration(array $payload, string $name, array &$session): void
    {
        $challenge = $this->takeChallenge('register', $session);
        $data = $this->webAuthn->processCreate(
            self::base64UrlDecode((string) ($payload['clientDataJSON'] ?? '')),
            self::base64UrlDecode((string) ($payload['attestationObject'] ?? '')),
            $challenge,
            true
        );

        $this->repository->add(
            self::base64UrlEncode((string) $data->credentialId),
            (string) $data->credentialPublicKey,
            (int) ($data->signatureCounter ?? 0),
            $name
        );
    }

    /**
     * @param array<string, mixed> $session
     */
    public function loginOptions(array &$session): object
    {
        $args = $this->webAuthn->getGetArgs([], self::TIMEOUT, true, true, true, true, true, true);
        $this->storeChallenge('login', $session);

        return $args;
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $session
     *
     * @throws WebAuthnException
     */
    public function finishLogin(array $payload, array &$session): void
    {
        $challenge = $this->takeChallenge('login', $session);
        $id = (string) ($payload['id'] ?? '');
        $credential = '' === $id ? null : $this->repository->find($id);
        if (null === $credential) {
            throw new WebAuthnException('unknown passkey');
        }

        $this->webAuthn->processGet(
            self::base64UrlDecode((string) ($payload['clientDataJSON'] ?? '')),
            self::base64UrlDecode((string) ($payload['authenticatorData'] ?? '')),
            self::base64UrlDecode((string) ($payload['signature'] ?? '')),
            $credential['publicKey'],
            $challenge,
            $credential['signCount'] > 0 ? $credential['signCount'] : null,
            true
        );

        $this->repository->markUsed($credential['id'], (int) ($this->webAuthn->getSignatureCounter() ?? 0));
    }

    public static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function base64UrlDecode(string $data): string
    {
        return (string) base64_decode(strtr($data, '-_', '+/'), true);
    }

    /**
     * @param array<string, mixed> $session
     */
    private function storeChallenge(string $type, array &$session): void
    {
        $session[self::SESSION_KEY] = [
            'type' => $type,
            'challenge' => $this->webAuthn->getChallenge()->getBinaryString(),
            'expires' => time() + self::CHALLENGE_TTL,
        ];
    }

    /**
     * @param array<string, mixed> $session
     *
     * @throws WebAuthnException
     */
    private function takeChallenge(string $type, array &$session): string
    {
        $pending = $session[self::SESSION_KEY] ?? null;
        unset($session[self::SESSION_KEY]);

        if (!is_array($pending) || ($pending['type'] ?? null) !== $type || ($pending['expires'] ?? 0) < time()) {
            throw new WebAuthnException('passkey challenge missing or expired');
        }

        return (string) $pending['challenge'];
    }
}
