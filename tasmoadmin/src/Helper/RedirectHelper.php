<?php

namespace TasmoAdmin\Helper;

class RedirectHelper
{
    private string $basePath;


    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    public function getValidRedirectUrl(string $url, string $fallbackUrl): string
    {
        if (!str_starts_with($url, '/')) {
            return $fallbackUrl;
        }

        if (str_starts_with($url, '//')) {
            return $fallbackUrl;
        }

        if (!str_starts_with($url, $this->basePath)) {
            return $fallbackUrl;
        }

        return $url;
    }
}
