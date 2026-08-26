<?php

namespace TasmoAdmin\Helper;

use Symfony\Component\HttpFoundation\Request;

class RequestHelper
{
    public const CSRF_TOKEN_FIELD = 'csrf_token';

    private const PUBLIC_I18N_QUERY_KEYS = ['i18n', 'lang'];

    private const LEGACY_MUTATION_QUERY_KEYS = ['removeDevices', 'doAjax', 'clean', 'selfupdate', 'auto'];

    public static function csrfToken(): string
    {
        if (empty($_SESSION[self::CSRF_TOKEN_FIELD])) {
            $_SESSION[self::CSRF_TOKEN_FIELD] = bin2hex(random_bytes(32));
        }

        return (string) $_SESSION[self::CSRF_TOKEN_FIELD];
    }

    public static function csrfTokenField(): string
    {
        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            self::CSRF_TOKEN_FIELD,
            htmlspecialchars(self::csrfToken(), ENT_QUOTES, 'UTF-8')
        );
    }

    public static function hasValidCsrfToken(Request $request): bool
    {
        $token = $request->request->get(self::CSRF_TOKEN_FIELD);

        return $request->isMethod('POST')
            && is_string($token)
            && isset($_SESSION[self::CSRF_TOKEN_FIELD])
            && hash_equals((string) $_SESSION[self::CSRF_TOKEN_FIELD], $token);
    }

    public static function sameSiteLaxCookieParams(array $params): array
    {
        $params['samesite'] = 'Lax';

        return $params;
    }

    public static function isLegacyMutationRequest(Request $request, string $route, ?string $action = null): bool
    {
        if ($request->isMethod('POST')) {
            return false;
        }

        if ('device_action' === $route && 'delete' === $action) {
            return true;
        }

        if ('actions' === $route) {
            return [] !== array_intersect(self::LEGACY_MUTATION_QUERY_KEYS, array_keys($request->query->all()));
        }

        return ('selfupdate' === $route && $request->query->has('selfupdate'))
            || ('upload' === $route && $request->query->has('auto'));
    }

    public static function isUnfamiliarUpdateSource(string $releaseUrl, string $officialReleaseUrl, string $currentHost): bool
    {
        $releaseHost = parse_url($releaseUrl, PHP_URL_HOST);

        return $releaseUrl !== $officialReleaseUrl
            && (!is_string($releaseHost)
                || '' === $currentHost
                || 0 !== strcasecmp($releaseHost, $currentHost));
    }

    public static function isPublicI18nRequest(Request $request): bool
    {
        if ('actions' !== trim($request->getPathInfo(), '/')
            || !$request->query->has('i18n')
            || !$request->isMethod('GET')
        ) {
            return false;
        }

        $queryKeys = array_keys($request->query->all());
        sort($queryKeys);
        $allowedKeys = self::PUBLIC_I18N_QUERY_KEYS;
        sort($allowedKeys);

        return [] === array_diff($queryKeys, $allowedKeys);
    }
}
