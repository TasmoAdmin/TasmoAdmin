<?php

namespace Tests\TasmoAdmin\Helper;

use lbuchs\WebAuthn\WebAuthnException;
use PHPUnit\Framework\TestCase;
use TasmoAdmin\Helper\PasskeyHelper;
use TasmoAdmin\PasskeyRepository;

class PasskeyHelperTest extends TestCase
{
    private PasskeyHelper $helper;

    protected function setUp(): void
    {
        $repository = new PasskeyRepository(sys_get_temp_dir().'/tasmoadmin-passkeys-'.uniqid().'/passkeys.json');
        $this->helper = new PasskeyHelper($repository, 'ta.example.com');
    }

    public function testRegistrationOptionsRequireResidentKeyAndStoreChallenge(): void
    {
        $session = [];
        $options = json_decode(json_encode($this->helper->registrationOptions('admin', $session)), true);

        self::assertSame('ta.example.com', $options['publicKey']['rp']['id']);
        self::assertSame('required', $options['publicKey']['authenticatorSelection']['residentKey']);
        self::assertSame('required', $options['publicKey']['authenticatorSelection']['userVerification']);
        self::assertSame('none', $options['publicKey']['attestation']);
        self::assertSame('register', $session['passkey_challenge']['type']);
        self::assertSame(
            $options['publicKey']['challenge'],
            PasskeyHelper::base64UrlEncode($session['passkey_challenge']['challenge'])
        );
    }

    public function testLoginRejectsChallengeFromOtherCeremony(): void
    {
        $session = [];
        $this->helper->registrationOptions('admin', $session);

        $this->expectException(WebAuthnException::class);

        try {
            $this->helper->finishLogin(['id' => 'x'], $session);
        } finally {
            self::assertArrayNotHasKey('passkey_challenge', $session);
        }
    }

    public function testLoginRejectsExpiredChallenge(): void
    {
        $session = [];
        $this->helper->loginOptions($session);
        $session['passkey_challenge']['expires'] = time() - 1;

        $this->expectException(WebAuthnException::class);
        $this->helper->finishLogin(['id' => 'x'], $session);
    }

    public function testLoginRejectsUnknownCredential(): void
    {
        $session = [];
        $this->helper->loginOptions($session);

        $this->expectException(WebAuthnException::class);
        $this->helper->finishLogin(['id' => 'unknown'], $session);
    }

    public function testBase64UrlRoundTrip(): void
    {
        $binary = random_bytes(33);

        self::assertSame($binary, PasskeyHelper::base64UrlDecode(PasskeyHelper::base64UrlEncode($binary)));
        self::assertStringNotContainsString('=', PasskeyHelper::base64UrlEncode($binary));
    }
}
