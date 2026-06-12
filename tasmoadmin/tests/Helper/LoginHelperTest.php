<?php

namespace Tests\TasmoAdmin\Helper;

use PHPUnit\Framework\TestCase;
use TasmoAdmin\Config;
use TasmoAdmin\Helper\LoginHelper;

class LoginHelperTest extends TestCase
{
    public function testRegister(): void
    {
        $configMock = $this->createMock(Config::class);
        $configMock->expects($this->exactly(2))->method('write');
        $loginHelper = new LoginHelper($configMock);
        $loginHelper->register('test-user', 'test-password');
    }

    public function testLoginValid(): void
    {
        $configMock = $this->createMock(Config::class);
        $loginHelper = new LoginHelper($configMock);

        $password = 'top-secret';
        $storedPassword = password_hash($password, PASSWORD_DEFAULT);

        self::assertTrue($loginHelper->login($password, $storedPassword));
    }

    public function testLoginInvalid(): void
    {
        $configMock = $this->createMock(Config::class);
        $loginHelper = new LoginHelper($configMock);

        $password = 'wrong-password';
        $storedPassword = password_hash('top-secret', PASSWORD_DEFAULT);

        self::assertFalse($loginHelper->login($password, $storedPassword));
    }

    public function testLoginUpgrade(): void
    {
        $configMock = $this->createMock(Config::class);
        $loginHelper = new LoginHelper($configMock);
        $password = 'top-secret';
        $storedPassword = md5($password);
        $configMock->expects($this->once())->method('write');
        self::assertTrue($loginHelper->login($password, $storedPassword));
    }

    public function testLoginRejectsInvalidLegacyMd5WithoutUpgrading(): void
    {
        $configMock = $this->createMock(Config::class);
        $configMock->expects($this->never())->method('write');

        $loginHelper = new LoginHelper($configMock);

        self::assertFalse($loginHelper->login('wrong-password', md5('top-secret')));
    }

    public function testRegisterStoresHashedPasswordInsteadOfPlaintext(): void
    {
        $configMock = $this->createMock(Config::class);
        $configMock->expects($this->exactly(2))
            ->method('write')
            ->willReturnCallback(static function (string $key, string $value): void {
                static $writes = [];
                $writes[$key] = $value;

                if (2 === count($writes)) {
                    TestCase::assertSame('test-user', $writes['username']);
                    TestCase::assertNotSame('test-password', $writes['password']);
                    TestCase::assertTrue(password_verify('test-password', $writes['password']));
                }
            })
        ;

        $loginHelper = new LoginHelper($configMock);
        $loginHelper->register('test-user', 'test-password');
    }
}
