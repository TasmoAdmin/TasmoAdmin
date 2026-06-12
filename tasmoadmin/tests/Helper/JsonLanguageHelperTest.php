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
        if (null !== $this->tempDir && is_dir($this->tempDir)) {
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

    public function testDumpJsonSkipsRewritingFreshCache(): void
    {
        $this->tempDir = sys_get_temp_dir().'/json-language-helper-'.bin2hex(random_bytes(8));
        mkdir($this->tempDir);

        $languageFile = $this->tempDir.'/language_de.ini';
        $fallbackLanguageFile = $this->tempDir.'/language_en.ini';
        $cacheDir = $this->tempDir.'/cache';
        mkdir($cacheDir);

        file_put_contents($languageFile, "HELLO = \"hallo\"\n");
        touch($languageFile, time() - 20);

        file_put_contents($fallbackLanguageFile, "HELLO = \"hello\"\n[WORLD]\nWORLD = \"world\"\n");
        touch($fallbackLanguageFile, time() - 15);

        $cacheFile = $cacheDir.'/json_i18n_de.cache.json';
        $expectedJson = json_encode([
            'cached' => true,
        ], JSON_THROW_ON_ERROR);
        file_put_contents($cacheFile, $expectedJson);
        touch($cacheFile, time() - 5);
        clearstatcache(true, $cacheFile);
        $modifiedAtBeforeDump = filemtime($cacheFile);

        $jsonLanguageHelper = new JsonLanguageHelper(
            'de',
            $languageFile,
            'en',
            $fallbackLanguageFile,
            $cacheDir
        );

        $jsonLanguageHelper->dumpJson();
        clearstatcache(true, $cacheFile);

        self::assertSame($expectedJson, file_get_contents($cacheFile));
        self::assertSame($modifiedAtBeforeDump, filemtime($cacheFile));
    }

    public function testDumpJsonUsesLanguageSpecificCacheFileAndFlattensBlocks(): void
    {
        $this->tempDir = sys_get_temp_dir().'/json-language-helper-'.bin2hex(random_bytes(8));
        mkdir($this->tempDir);

        $languageFile = $this->tempDir.'/language_fr.ini';
        $fallbackLanguageFile = $this->tempDir.'/language_en.ini';
        $cacheDir = $this->tempDir.'/cache';
        mkdir($cacheDir);

        file_put_contents($languageFile, "BONJOUR = \"bonjour\"\n[GROUP]\nSALUT = \"salut\"\n");
        file_put_contents($fallbackLanguageFile, "HELLO = \"hello\"\n[WORLD]\nWORLD = \"world\"\n");

        $jsonLanguageHelper = new JsonLanguageHelper(
            'fr',
            $languageFile,
            'en',
            $fallbackLanguageFile,
            $cacheDir
        );

        $jsonLanguageHelper->dumpJson();

        self::assertEquals([
            'fr' => [
                'BONJOUR' => 'bonjour',
                'SALUT' => 'salut',
            ],
            'en' => [
                'HELLO' => 'hello',
                'WORLD' => 'world',
            ],
        ], json_decode(file_get_contents($cacheDir.'/json_i18n_fr.cache.json'), true));
    }

    public function testDumpJsonFlattensArrayEntriesFromIniFiles(): void
    {
        $this->tempDir = sys_get_temp_dir().'/json-language-helper-'.bin2hex(random_bytes(8));
        mkdir($this->tempDir);

        $languageFile = $this->tempDir.'/language_custom.ini';
        $fallbackLanguageFile = $this->tempDir.'/language_en.ini';
        $cacheDir = $this->tempDir.'/cache';
        mkdir($cacheDir);

        file_put_contents($languageFile, "ITEM[] = \"eins\"\nITEM[] = \"zwei\"\n");
        file_put_contents($fallbackLanguageFile, "HELLO = \"hello\"\n");

        $jsonLanguageHelper = new JsonLanguageHelper(
            'custom',
            $languageFile,
            'en',
            $fallbackLanguageFile,
            $cacheDir
        );

        $jsonLanguageHelper->dumpJson();

        self::assertEquals([
            'custom' => [
                0 => 'eins',
                1 => 'zwei',
            ],
            'en' => [
                'HELLO' => 'hello',
            ],
        ], json_decode(file_get_contents($cacheDir.'/json_i18n_custom.cache.json'), true));
    }

    private function removeDirectory(string $directory): void
    {
        $entries = scandir($directory);
        if (false === $entries) {
            return;
        }

        foreach ($entries as $entry) {
            if ('.' === $entry || '..' === $entry) {
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
