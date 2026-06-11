<?php

declare(strict_types=1);

namespace Tests\TasmoAdmin\Page;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TasmoAdmin\Device;

class DevicesTableSortableColumnsTest extends TestCase
{
    #[DataProvider('sortableColumnProvider')]
    public function testDevicesTableMarksExpectedColumnsAsSortable(string $columnId): void
    {
        if (!defined('_BASEURL_')) {
            define('_BASEURL_', '/');
        }
        if (!defined('_RESOURCESURL_')) {
            define('_RESOURCESURL_', '/resources/');
        }

        $devices = [
            new Device(1, ['Desk Lamp'], '192.168.1.10', '', ''),
        ];
        $deviceLinks = false;
        $deviceLinksHideClass = '';

        ob_start();

        include __DIR__.'/../../pages/elements/devices_table.php';
        $output = ob_get_clean();

        self::assertIsString($output);
        self::assertMatchesRegularExpression(
            sprintf(
                "/<th[^>]*data-column-id='%s'[^>]*data-tablesaw-sortable-col/s",
                preg_quote($columnId, '/')
            ),
            $output
        );
    }

    public static function sortableColumnProvider(): array
    {
        return [
            ['id'],
            ['position'],
            ['name'],
            ['ip'],
            ['rssi'],
            ['version'],
            ['runtime'],
            ['energyPower'],
            ['temp'],
            ['humidity'],
            ['illuminance'],
            ['hostname'],
            ['mac'],
            ['mqtt'],
            ['idx'],
            ['poweronstate'],
            ['ledstate'],
            ['savedata'],
            ['sleep'],
            ['bootcount'],
            ['savecount'],
            ['log'],
            ['wificonfig'],
            ['vcc'],
        ];
    }
}
