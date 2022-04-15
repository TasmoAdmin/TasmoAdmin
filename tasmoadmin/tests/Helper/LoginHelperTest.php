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
}
