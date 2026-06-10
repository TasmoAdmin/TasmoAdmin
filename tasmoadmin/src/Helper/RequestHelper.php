<?php

namespace TasmoAdmin\Helper;

use Symfony\Component\HttpFoundation\Request;

class RequestHelper
{
    public static function isPublicI18nRequest(Request $request): bool
    {
        return 'actions' === trim($request->getPathInfo(), '/')
            && $request->query->has('i18n');
    }
}
