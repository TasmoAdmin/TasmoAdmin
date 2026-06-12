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

    public function testDevicesTableRendersCurrentHeaderControls(): void
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
        $deviceLinks = true;
        $deviceLinksHideClass = '';

        ob_start();

        include __DIR__.'/../../pages/elements/devices_table.php';
        $output = ob_get_clean();

        self::assertIsString($output);
        self::assertSame(0, substr_count($output, '<tfoot>'));
        self::assertSame(1, substr_count($output, 'id="select_all"'));
    }

    public function testDevicesTableDoesNotUseBrokenStackLayout(): void
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
        $deviceLinks = true;
        $deviceLinksHideClass = '';

        ob_start();

        include __DIR__.'/../../pages/elements/devices_table.php';
        $output = ob_get_clean();

        self::assertIsString($output);
        self::assertStringContainsString('tablesaw tablesaw-stack', $output);
        self::assertStringContainsString('data-tablesaw-mode="stack"', $output);
        self::assertStringNotContainsString('data-tablesaw-mode="swipe"', $output);
    }

    public function testDevicesTableDeleteLinkOpensConfirmationModal(): void
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
        $deviceLinks = true;
        $deviceLinksHideClass = '';

        ob_start();

        include __DIR__.'/../../pages/elements/devices_table.php';
        $output = ob_get_clean();

        self::assertIsString($output);
        self::assertStringContainsString('data-bs-toggle="modal"', $output);
        self::assertStringContainsString('data-bs-target="#deleteDeviceModal"', $output);
        self::assertStringContainsString('data-dialog-action="delete"', $output);
        self::assertStringContainsString("data-dialog-title='", $output);
        self::assertStringContainsString('/device_action/delete/1', $output);
    }

    public function testDevicesTableRestartLinkOpensConfirmationModal(): void
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
        $deviceLinks = true;
        $deviceLinksHideClass = '';

        ob_start();

        include __DIR__.'/../../pages/elements/devices_table.php';
        $output = ob_get_clean();

        self::assertIsString($output);
        self::assertStringContainsString("class='restart-device'", $output);
        self::assertStringContainsString('data-bs-target="#deleteDeviceModal"', $output);
        self::assertStringContainsString('data-dialog-action="restart"', $output);
        self::assertStringContainsString("data-dialog-device-id='1'", $output);
    }
}
