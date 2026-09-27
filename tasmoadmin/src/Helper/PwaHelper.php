<?php

declare(strict_types=1);

namespace TasmoAdmin\Helper;

/**
 * Builds the web app manifest; paths depend on the base URL TasmoAdmin runs under.
 */
class PwaHelper
{
    public const BACKGROUND_COLOR = '#f3f5f9';

    public const THEME_COLOR = '#ffffff';

    /**
     * @return array<string, mixed>
     */
    public function manifest(string $baseUrl, string $resourceUrl, string $lang, string $description): array
    {
        $icons = $resourceUrl.'img/favicons/';

        return [
            'id' => $baseUrl,
            'name' => 'TasmoAdmin',
            'short_name' => 'TasmoAdmin',
            'description' => $description,
            'lang' => $lang,
            'start_url' => $baseUrl,
            'scope' => $baseUrl,
            'display' => 'standalone',
            'background_color' => self::BACKGROUND_COLOR,
            'theme_color' => self::THEME_COLOR,
            'icons' => [
                ['src' => $icons.'android-chrome-192x192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => $icons.'android-chrome-512x512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => $icons.'maskable-192x192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'maskable'],
                ['src' => $icons.'maskable-512x512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
        ];
    }
}
