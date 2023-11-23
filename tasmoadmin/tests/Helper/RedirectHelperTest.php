<?php

namespace Tests\TasmoAdmin\Helper;
use PHPUnit\Framework\Attributes\DataProvider;
use TasmoAdmin\Helper\RedirectHelper;
use PHPUnit\Framework\TestCase;



class RedirectHelperTest extends TestCase
{
    #[DataProvider('validDataProvider')]
    public function testGetValidRedirectUrlValid(string $basePath, string $url, string $fallbackUrl): void
    {
        $redirectHelper = new RedirectHelper($basePath);
        self::assertEquals($url, $redirectHelper->getValidRedirectUrl($url, $fallbackUrl));
    }

    public static function validDataProvider(): array
    {
        return [
          ['/', '/foo/bar', '/'],
        ];
    }

    #[DataProvider('invalidDataProvider')]
    public function testGetValidRedirectUrlInvalid(string $basePath, string $url, string $fallbackUrl): void
    {
        $redirectHelper = new RedirectHelper($basePath);
        self::assertEquals($fallbackUrl, $redirectHelper->getValidRedirectUrl($url, $fallbackUrl));
    }

    public static function invalidDataProvider(): array
    {
        return [
            ['/', 'http://bad.com/foo/bar', '/'],
            ['/', '//bad.com/foo/bar', '/'],
            ['/', '://bad.com/foo/bar', '/'],
        ];
    }
}
