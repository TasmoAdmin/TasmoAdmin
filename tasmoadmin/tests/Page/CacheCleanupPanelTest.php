<?php

namespace Tests\TasmoAdmin\Page;

use PHPUnit\Framework\TestCase;

class CacheCleanupPanelTest extends TestCase
{
    public function testPanelRendersSafeMaintenanceCopyAndButton(): void
    {
        ob_start();

        include __DIR__.'/../../pages/elements/cache_cleanup_panel.php';
        $output = (string) ob_get_clean();

        self::assertStringContainsString('USER_CONFIG_CONFIG_MAINTENANCE_TITLE:', $output);
        self::assertStringContainsString('USER_CONFIG_CONFIG_CACHE_CLEAR_SCOPE:', $output);
        self::assertStringContainsString('USER_CONFIG_CONFIG_CACHE_CLEAR_SAFE:', $output);
        self::assertStringContainsString('name="clean_temp_cache"', $output);
        self::assertStringContainsString('USER_CONFIG_BTN_CLEAR_CACHE:', $output);
    }
}
