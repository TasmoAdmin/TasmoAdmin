<?php

namespace Tests\TasmoAdmin\Helper;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use TasmoAdmin\Helper\JsonLanguageHelper;
use PHPUnit\Framework\TestCase;
use Tests\TasmoAdmin\TestUtils;

class JsonLanguageHelperTest extends TestCase
{
    private vfsStreamDirectory $root;

    public function setUp(): void
    {
        $this->root = vfsStream::setup();
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
}
