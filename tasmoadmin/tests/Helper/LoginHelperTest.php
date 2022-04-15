<?php

namespace Tests\TasmoAdmin\Helper;

use PHPUnit\Framework\TestCase;
use TasmoAdmin\Config;
use TasmoAdmin\Helper\LoginHelper;

class LoginHelperTest extends TestCase
{
    public function testRegisterTest() {
        $configMock = $this->createMock(Config::class);
        $configMock->expects($this->exactly(2))->method('write');
        $loginHelper = new LoginHelper($configMock);
        $loginHelper->register('test-user', 'test-password');
    }
}
