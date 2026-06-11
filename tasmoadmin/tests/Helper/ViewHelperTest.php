<?php

namespace Tests\TasmoAdmin\Helper;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use TasmoAdmin\Config;
use TasmoAdmin\Helper\ViewHelper;

class ViewHelperTest extends TestCase
{
    public function testGetNightModeReturnsEmptyStringWhenDisabled(): void
    {
        $helper = new ViewHelper($this->getConfig(['nightmode' => 'disable']));

        self::assertSame('', $helper->getNightMode(22));
    }

    public function testGetNightModeReturnsNightmodeDuringAutoNightHours(): void
    {
        $helper = new ViewHelper($this->getConfig(['nightmode' => 'auto']));

        self::assertSame('nightmode', $helper->getNightMode(18));
        self::assertSame('', $helper->getNightMode(12));
    }

    public function testGetNightModeReturnsNightmodeWhenAlwaysEnabled(): void
    {
        $helper = new ViewHelper($this->getConfig(['nightmode' => 'always']));

        self::assertSame('nightmode', $helper->getNightMode(12));
    }

    public function testGetValueFormatsBooleansAndScalars(): void
    {
        $helper = new ViewHelper($this->getConfig([]));

        self::assertSame('true', $helper->getValue(true));
        self::assertSame('false', $helper->getValue(false));
        self::assertSame('42', $helper->getValue(42));
    }

    private function getConfig(array $overrides): Config
    {
        $root = vfsStream::setup('view-helper-'.bin2hex(random_bytes(4)));
        $config = new Config($root->url().'/', $root->url().'/');
        $config->writeAll($overrides);

        return $config;
    }
}
