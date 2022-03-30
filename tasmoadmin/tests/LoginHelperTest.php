<?php

namespace Tests\TasmoAdmin;

use \Config;
use \LoginHelper;
use PHPUnit\Framework\TestCase;

class LoginHelperTest extends TestCase
{
    public function testRegisterTest() {
        $configMock = $this->createMock(Config::class);
        $configMock->expects($this->exactly(2))->method('write');
        $loginHelper = new LoginHelper($configMock);
        $loginHelper->register('test-user', 'test-password');
    }
}
