<?php

declare(strict_types=1);

namespace Tests\TasmoAdmin\Helper;

use PHPUnit\Framework\TestCase;
use TasmoAdmin\Helper\PwaHelper;

class PwaHelperTest extends TestCase
{
    public function testManifestIsScopedToBaseUrl(): void
    {
        $manifest = new PwaHelper()->manifest('/tasmo/', '/tasmo/resources/', 'de', 'Beschreibung');

        self::assertSame('/tasmo/', $manifest['start_url']);
        self::assertSame('/tasmo/', $manifest['scope']);
        self::assertSame('/tasmo/', $manifest['id']);
        self::assertSame('standalone', $manifest['display']);
        self::assertSame('de', $manifest['lang']);
        self::assertSame('Beschreibung', $manifest['description']);
    }

    public function testManifestListsInstallableAndMaskableIcons(): void
    {
        $manifest = new PwaHelper()->manifest('/', '/resources/', 'en', '');
        $icons = $manifest['icons'];

        self::assertContains('/resources/img/favicons/android-chrome-512x512.png', array_column($icons, 'src'));
        self::assertSame(['any', 'any', 'maskable', 'maskable'], array_column($icons, 'purpose'));
        foreach ($icons as $icon) {
            self::assertFileExists(dirname(__DIR__, 2).$icon['src']);
        }
    }
}
