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
            'en',
            TestUtils::getFixturePath('language_en.ini'),
            $this->root->url()
        );

        $jsonLanguageHelper->dumpJson();
        self::assertTrue($this->root->hasChild('json_i18n_en.cache.json'));
        self::assertEquals(['en' => [
            'HELLO' => 'hello',
            'WORLD' => 'world',
        ]], json_decode($this->root->getChild('json_i18n_en.cache.json')->getContent(), true));
    }
}
