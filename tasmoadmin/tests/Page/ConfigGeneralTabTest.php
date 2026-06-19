<?php

declare(strict_types=1);

namespace Tests\TasmoAdmin\Page;

use PHPUnit\Framework\TestCase;

class ConfigGeneralTabTest extends TestCase
{
    public function testGeneralTabUsesSharedCardLayout(): void
    {
        $status = new \stdClass();
        $status->Status = (object) [
            'FriendlyName' => ['Desk Lamp', 'Shelf Lamp'],
            'PowerOnState' => 2,
            'LedState' => 3,
        ];
        $status->StatusPRM = (object) [
            'Sleep' => 50,
        ];

        ob_start();

        include __DIR__.'/../../pages/device_config_tabs/config_general_tab.php';
        $output = ob_get_clean();

        self::assertIsString($output);
        self::assertStringContainsString('device-config-card', $output);
        self::assertStringContainsString('DEVICE_CONFIG_TAB_HL_GENERAL', $output);
        self::assertStringContainsString("name='FriendlyName1'", $output);
    }
}
