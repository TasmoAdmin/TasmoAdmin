<?php

namespace Tests\TasmoAdmin;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use TasmoAdmin\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    private vfsStreamDirectory $root;

    protected function setUp(): void
    {
        $this->root = vfsStream::setup('config');
    }

    public function testReadInvalidKey(): void
    {
        $config = $this->getConfig();
        self::assertNull($config->read('random_key'));
    }

    public function testReadValidKey(): void
    {
        $config = $this->getConfig();
        self::assertEquals('1', $config->read('hide_copyright'));
    }

    public function testWrite(): void
    {
        $config = $this->getConfig();
        $config->write('hide_copyright', '0');
        self::assertEquals('0', $config->read('hide_copyright'));
    }

    public function testWriteRemove(): void
    {
        $config = $this->getConfig();
        $config->write('hide_copyright', null);
        self::assertNull($config->read('hide_copyright'));
    }

    public function testWriteDefault(): void
    {
        $config = $this->getConfig();
        $config->write('hide_copyright', '0');
        self::assertEquals('0', $config->read('hide_copyright'));
        $config->write('hide_copyright', 0);
        self::assertEquals('1', $config->read('hide_copyright'));
    }

    public function testWriteAll(): void
    {
        $config = $this->getConfig();
        $config->writeAll(['hide_copyright' => '0', 'homepage' => 'devices']);
        self::assertEquals('0', $config->read('hide_copyright'));
        self::assertEquals('devices', $config->read('homepage'));
    }

    public function testClean(): void
    {
        $config = $this->getConfig();
        $config->write('random_key', '123');
        self::assertEquals('123', $config->read('random_key'));
        $config->clean();
        self::assertNull($config->read('random_key'));
    }

    private function getConfig(): Config
    {
        return new Config($this->root->url() . '/', $this->root->url() . '/');
    }
}
