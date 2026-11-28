<?php

namespace Tests\TasmoAdmin\Helper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use TasmoAdmin\Helper\RequestHelper;

class RequestHelperTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_SESSION[RequestHelper::CSRF_TOKEN_FIELD]);
    }

    public function testCsrfTokenIsStableForTheSession(): void
    {
        $first = RequestHelper::csrfToken();

        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $first);
        self::assertSame($first, RequestHelper::csrfToken());
        self::assertStringContainsString('name="csrf_token"', RequestHelper::csrfTokenField());
    }

    public function testCsrfValidationRejectsMissingMismatchedAndGetTokens(): void
    {
        $_SESSION[RequestHelper::CSRF_TOKEN_FIELD] = 'expected-token';

        self::assertFalse(RequestHelper::hasValidCsrfToken(Request::create('/actions', 'POST')));
        self::assertFalse(RequestHelper::hasValidCsrfToken(Request::create('/actions', 'POST', ['csrf_token' => 'wrong-token'])));
        self::assertFalse(RequestHelper::hasValidCsrfToken(Request::create('/actions', 'GET', ['csrf_token' => 'expected-token'])));
    }

    public function testCsrfValidationAcceptsMatchingPostToken(): void
    {
        $_SESSION[RequestHelper::CSRF_TOKEN_FIELD] = 'expected-token';

        self::assertTrue(RequestHelper::hasValidCsrfToken(Request::create('/actions', 'POST', ['csrf_token' => 'expected-token'])));
    }

    public function testSameSiteCookieParamsSetHttpOnlyAndSecureForHttps(): void
    {
        self::assertSame(
            ['path' => '/tasmo', 'secure' => true, 'httponly' => true, 'samesite' => 'Lax'],
            RequestHelper::sameSiteCookieParams(['path' => '/tasmo', 'secure' => false], false, true)
        );
    }

    public function testCrossSiteIframeCookieParamsRequireSecureSameSiteNone(): void
    {
        self::assertSame(
            ['path' => '/tasmo', 'secure' => true, 'httponly' => true, 'samesite' => 'None'],
            RequestHelper::sameSiteCookieParams(['path' => '/tasmo', 'secure' => false], true, true)
        );
    }

    public function testCrossSiteIframeRequiresHttps(): void
    {
        self::assertTrue(RequestHelper::isHttpsRequest(['HTTPS' => 'on']));
        self::assertTrue(RequestHelper::isHttpsRequest(['SERVER_PORT' => '443']));
        self::assertFalse(RequestHelper::isHttpsRequest(['HTTPS' => 'off', 'SERVER_PORT' => '80']));
    }

    public function testLegacyMutationRequestsAreRejectedOutsidePost(): void
    {
        self::assertTrue(RequestHelper::isLegacyMutationRequest(Request::create('/actions?doAjax=1'), 'actions'));
        self::assertTrue(RequestHelper::isLegacyMutationRequest(Request::create('/actions?doAjaxAll=1'), 'actions'));
        self::assertTrue(RequestHelper::isLegacyMutationRequest(Request::create('/actions?clean=config'), 'actions'));
        self::assertTrue(RequestHelper::isLegacyMutationRequest(Request::create('/device_action/delete/1'), 'device_action', 'delete'));
        self::assertTrue(RequestHelper::isLegacyMutationRequest(Request::create('/selfupdate?selfupdate=1'), 'selfupdate'));
        self::assertFalse(RequestHelper::isLegacyMutationRequest(Request::create('/actions', 'POST', ['doAjax' => 1]), 'actions'));
    }

    public function testUnfamiliarUpdateSourcesRequireConfirmation(): void
    {
        self::assertFalse(RequestHelper::isUnfamiliarUpdateSource(
            'https://github.com/TasmoAdmin/TasmoAdmin/releases/download/v1/release.zip',
            'https://github.com/TasmoAdmin/TasmoAdmin/releases/download/v1/release.zip',
            'tasmoadmin.example'
        ));
        self::assertFalse(RequestHelper::isUnfamiliarUpdateSource(
            'https://updates.example/release.zip',
            'https://github.com/TasmoAdmin/TasmoAdmin/releases/download/v1/release.zip',
            'updates.example'
        ));
        self::assertTrue(RequestHelper::isUnfamiliarUpdateSource(
            'https://third-party.example/release.zip',
            'https://github.com/TasmoAdmin/TasmoAdmin/releases/download/v1/release.zip',
            'updates.example'
        ));
    }

    #[DataProvider('allowedUpdateSourceProvider')]
    public function testIsAllowedUpdateSourceAccepts(string $releaseUrl, string $officialReleaseUrl): void
    {
        self::assertTrue(RequestHelper::isAllowedUpdateSource($releaseUrl, $officialReleaseUrl));
    }

    public static function allowedUpdateSourceProvider(): array
    {
        $officialReleaseUrl = 'https://github.com/TasmoAdmin/TasmoAdmin/releases/download/v1/release.zip';

        return [
            'official release url exact match' => [$officialReleaseUrl, $officialReleaseUrl],
            'github release download' => [$officialReleaseUrl, ''],
            'www.github.com host' => ['https://www.github.com/tasmoadmin/tasmoadmin/releases/download/v1/release.zip', ''],
            'case-insensitive host and path' => ['https://GITHUB.COM/TasmoAdmin/TasmoAdmin/releases/download/v1/release.zip', ''],
        ];
    }

    #[DataProvider('rejectedUpdateSourceProvider')]
    public function testIsAllowedUpdateSourceRejects(string $releaseUrl, string $officialReleaseUrl): void
    {
        self::assertFalse(RequestHelper::isAllowedUpdateSource($releaseUrl, $officialReleaseUrl));
    }

    public static function rejectedUpdateSourceProvider(): array
    {
        $officialReleaseUrl = 'https://github.com/TasmoAdmin/TasmoAdmin/releases/download/v1/release.zip';

        return [
            'http scheme' => ['http://github.com/tasmoadmin/tasmoadmin/releases/download/v1/release.zip', $officialReleaseUrl],
            'non-github host' => ['https://evil.example/tasmoadmin/tasmoadmin/releases/download/v1/release.zip', $officialReleaseUrl],
            'github subdomain lookalike' => ['https://github.com.evil.example/tasmoadmin/tasmoadmin/releases/download/v1/release.zip', $officialReleaseUrl],
            'wrong repository path' => ['https://github.com/other/repo/releases/download/v1/release.zip', $officialReleaseUrl],
            'repository path without trailing slash' => ['https://github.com/tasmoadmin/tasmoadmin', $officialReleaseUrl],
            'encoded traversal escaping prefix' => ['https://github.com/tasmoadmin/%2e%2e/%2e%2e/evil.zip', $officialReleaseUrl],
            'control characters' => ["https://github.com/tasmoadmin/tasmoadmin/\x01release.zip", $officialReleaseUrl],
            'not a url' => ['not a url', $officialReleaseUrl],
            'empty url' => ['', $officialReleaseUrl],
        ];
    }

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

    public function testIsPublicI18nRequestReturnsFalseWhenPrivilegedQueryParamsArePresent(): void
    {
        $request = Request::create('/actions?i18n=1&doAjax=1&id=1');

        self::assertFalse(RequestHelper::isPublicI18nRequest($request));
    }

    public function testIsPublicI18nRequestReturnsTrueWithoutOptionalLangParameter(): void
    {
        $request = Request::create('/actions?i18n=1');

        self::assertTrue(RequestHelper::isPublicI18nRequest($request));
    }

    public function testIsPublicI18nRequestReturnsFalseForNonGetMethods(): void
    {
        $request = Request::create('/actions?i18n=1', 'POST');

        self::assertFalse(RequestHelper::isPublicI18nRequest($request));
    }
}
