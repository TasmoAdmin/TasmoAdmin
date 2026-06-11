<?php

namespace Tests\TasmoAdmin;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use TasmoAdmin\Config;

class ConfigTest extends TestCase
{
    private vfsStreamDirectory $root;

    private string $originalPath;

    protected function setUp(): void
    {
        $this->root = vfsStream::setup('config');
        $this->originalPath = getenv('PATH') ?: '';
    }

    protected function tearDown(): void
    {
        putenv('PATH='.$this->originalPath);
        putenv('BUILD_VERSION');
    }

    public function testConstructor(): void
    {
        $config = $this->getConfig();
        self::assertEquals('auto', $config->read('nightmode'));
        self::assertEquals('0', $config->read('confirm_device_toggles'));
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

    public function testReadEscaped(): void
    {
        $config = $this->getConfig();
        $config->write('escaped', "alert('XSS')");
        self::assertEquals('alert(&#039;XSS&#039;)', $config->read('escaped'));
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

    public function testConstructorFallsBackToGitDescribeWhenVersionSourcesAreMissing(): void
    {
        putenv('BUILD_VERSION');

        $appRoot = sys_get_temp_dir().'/tasmoadmin-config-'.bin2hex(random_bytes(6));
        $binDir = $appRoot.'/bin';
        $dataDir = $appRoot.'/data';
        mkdir($appRoot);
        mkdir($binDir);
        mkdir($dataDir);
        file_put_contents($binDir.'/git', "#!/bin/sh\nif [ \"$3\" = \"describe\" ]; then\n  echo test-dev-version\nelif [ \"$3\" = \"rev-parse\" ]; then\n  echo test-branch\nfi\n");
        chmod($binDir.'/git', 0o755);
        putenv('PATH='.$binDir.':'.$this->originalPath);

        try {
            $config = new Config($dataDir.'/', $appRoot.'/');

            self::assertEquals('test-dev-version', $config->read('current_git_tag'));
            self::assertEquals('test-branch', $config->read('current_git_branch'));
        } finally {
            if (file_exists($dataDir.'/MyConfig.json')) {
                unlink($dataDir.'/MyConfig.json');
            }
            unlink($binDir.'/git');
            rmdir($binDir);
            rmdir($dataDir);
            rmdir($appRoot);
        }
    }

    private function getConfig(): Config
    {
        return new Config($this->root->url().'/', $this->root->url().'/');
    }
}
