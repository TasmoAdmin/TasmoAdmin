<?php

namespace TasmoAdmin\Helper;

use Symfony\Component\HttpFoundation\Request;

class RequestHelper
{
    private const PUBLIC_I18N_QUERY_KEYS = ['i18n', 'lang'];

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
