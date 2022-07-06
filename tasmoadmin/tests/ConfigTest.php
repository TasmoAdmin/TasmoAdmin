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
		$config = new Config($this->root->url() . '/');
		self::assertNull($config->read('random_key'));
	}

	public function testReadValidKey(): void
	{
		$config = new Config($this->root->url() . '/');
		self::assertEquals('1', $config->read('hide_copyright'));
	}

    public function testWrite(): void
    {
        $config = new Config($this->root->url() . '/');
        $config->write('hide_copyright', '0');
        self::assertEquals('0', $config->read('hide_copyright'));
    }
}
