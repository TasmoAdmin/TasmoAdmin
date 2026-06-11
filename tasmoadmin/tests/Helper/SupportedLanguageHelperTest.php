<?php

namespace Tests\TasmoAdmin\Helper;

use PHPUnit\Framework\TestCase;
use TasmoAdmin\Helper\SupportedLanguageHelper;

class SupportedLanguageHelperTest extends TestCase
{
    public function testGetSupportedLanguagesReturnsExpectedKeys(): void
    {
        $languages = SupportedLanguageHelper::getSupportedLanguages();

        self::assertArrayHasKey('en', $languages);
        self::assertSame('English', $languages['en']);
        self::assertArrayHasKey('de', $languages);
        self::assertArrayHasKey('zh_TW', $languages);
        self::assertCount(12, $languages);
    }
}
