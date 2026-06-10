<?php

namespace Tests\TasmoAdmin\Helper;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use TasmoAdmin\Helper\JsonLanguageHelper;
use Tests\TasmoAdmin\TestUtils;

class JsonLanguageHelperTest extends TestCase
{
    private vfsStreamDirectory $root;

    private ?string $tempDir = null;

    public function setUp(): void
    {
        $this->root = vfsStream::setup();
    }

    public function tearDown(): void
    {
        if ($this->tempDir !== null && is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }
    }

    public function testDumpJson(): void
    {
        $jsonLanguageHelper = new JsonLanguageHelper(
            'de',
            TestUtils::getFixturePath('language_de.ini'),
            'en',
            TestUtils::getFixturePath('language_en.ini'),
            $this->root->url()
        );

        $jsonLanguageHelper->dumpJson();
        self::assertTrue($this->root->hasChild('json_i18n_de.cache.json'));
        self::assertEquals([
            'de' => [
                'HELLO' => 'hallo',
            ],
            'en' => [
                'HELLO' => 'hello',
                'WORLD' => 'world',
            ],
        ], json_decode(file_get_contents($this->root->getChild('json_i18n_de.cache.json')->url()), true));
    }

    public function testDumpJsonRegeneratesCacheWhenFallbackLanguageChanges(): void
    {
        $this->tempDir = sys_get_temp_dir().'/json-language-helper-'.bin2hex(random_bytes(8));
        mkdir($this->tempDir);

        $languageFile = $this->tempDir.'/language_de.ini';
        $fallbackLanguageFile = $this->tempDir.'/language_en.ini';
        $cacheDir = $this->tempDir.'/cache';
        mkdir($cacheDir);

        file_put_contents($languageFile, "HELLO = \"hallo\"\n");
        touch($languageFile, time() - 20);

        file_put_contents($fallbackLanguageFile, "HELLO = \"hello\"\nWORLD = \"world\"\n");
        touch($fallbackLanguageFile, time() - 5);

        $cacheFile = $cacheDir.'/json_i18n_de.cache.json';
        file_put_contents($cacheFile, json_encode([
            'de' => [
                'HELLO' => 'hallo',
            ],
            'en' => [
                'HELLO' => 'hello',
            ],
        ]));
        touch($cacheFile, time() - 10);

        $jsonLanguageHelper = new JsonLanguageHelper(
            'de',
            $languageFile,
            'en',
            $fallbackLanguageFile,
            $cacheDir
        );

        $jsonLanguageHelper->dumpJson();

        self::assertEquals([
            'de' => [
                'HELLO' => 'hallo',
            ],
            'en' => [
                'HELLO' => 'hello',
                'WORLD' => 'world',
            ],
        ], json_decode(file_get_contents($cacheFile), true));
    }

    private function removeDirectory(string $directory): void
    {
        $entries = scandir($directory);
        if ($entries === false) {
            return;
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $directory.'/'.$entry;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($directory);
    }
}
