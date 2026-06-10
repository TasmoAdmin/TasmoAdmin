<?php

namespace Tests\TasmoAdmin\Helper;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use TasmoAdmin\Helper\RequestHelper;

class RequestHelperTest extends TestCase
{
    public function testIsPublicI18nRequestReturnsTrueForActionsRouteWithI18nQuery(): void
    {
        $request = Request::create('/actions?i18n=1&lang=de');

        self::assertTrue(RequestHelper::isPublicI18nRequest($request));
    }

    public function testIsPublicI18nRequestReturnsFalseWhenI18nQueryIsMissing(): void
    {
        $request = Request::create('/actions');

        self::assertFalse(RequestHelper::isPublicI18nRequest($request));
    }

    public function testIsPublicI18nRequestReturnsFalseForOtherRoutes(): void
    {
        $request = Request::create('/login?i18n=1');

        self::assertFalse(RequestHelper::isPublicI18nRequest($request));
    }
}
