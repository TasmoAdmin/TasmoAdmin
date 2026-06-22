<?php

declare(strict_types=1);

namespace Tests\TasmoAdmin\Page;

use PHPUnit\Framework\TestCase;
use TasmoAdmin\Device;
use TasmoAdmin\Sonoff;

class ToggleConfirmAttributeRenderingTest extends TestCase
{
    public function testDevicesTableRendersDisabledDeviceConfirmationAsZero(): void
    {
        if (!defined('_BASEURL_')) {
            define('_BASEURL_', '/');
        }
        if (!defined('_RESOURCESURL_')) {
            define('_RESOURCESURL_', '/resources/');
        }

        $devices = [
            new Device(
                1,
                ['desk-lamp'],
                '192.168.1.10',
                '',
                '',
                deviceConfirmToggle: false
            ),
        ];
        $deviceLinks = false;
        $deviceLinksHideClass = '';

        ob_start();

        include __DIR__.'/../../pages/elements/devices_table.php';
        $output = ob_get_clean();

        self::assertIsString($output);
        self::assertStringContainsString("data-device_confirm_toggle='0'", $output);
    }

    public function testStartPageRendersDisabledDeviceConfirmationAsZero(): void
    {
        if (!defined('_RESOURCESURL_')) {
            define('_RESOURCESURL_', '/resources/');
        }

        $devices = [
            new Device(
                1,
                ['desk-lamp'],
                '192.168.1.10',
                '',
                '',
                deviceConfirmToggle: false
            ),
        ];

        $Config = new class {
            public function read(string $key): string
            {
                return 'disable';
            }
        };
        $urlHelper = new class {
            public function js(string $path): string
            {
                return '/js/'.$path.'.js';
            }
        };

        $container = new class($devices) {
            public function __construct(private array $devices) {}

            public function get(string $class): object
            {
                if (Sonoff::class !== $class) {
                    throw new \InvalidArgumentException('Unexpected class requested');
                }

                return new class($this->devices) {
                    public function __construct(private array $devices) {}

                    public function getDevices(): array
                    {
                        return $this->devices;
                    }
                };
            }
        };

        ob_start();

        include __DIR__.'/../../pages/start.php';
        $output = ob_get_clean();

        self::assertIsString($output);
        self::assertStringContainsString("data-device_confirm_toggle='0'", $output);
        self::assertStringContainsString('data-timer-indicator', $output);
    }

    public function testStartPageSkipsDevicesHiddenFromStartpage(): void
    {
        if (!defined('_RESOURCESURL_')) {
            define('_RESOURCESURL_', '/resources/');
        }

        $devices = [
            new Device(
                1,
                ['desk-lamp'],
                '192.168.1.10',
                '',
                '',
                deviceHideFromStartpage: false
            ),
            new Device(
                2,
                ['hidden-lamp'],
                '192.168.1.11',
                '',
                '',
                deviceHideFromStartpage: true
            ),
        ];

        $Config = new class {
            public function read(string $key): string
            {
                return 'disable';
            }
        };
        $urlHelper = new class {
            public function js(string $path): string
            {
                return '/js/'.$path.'.js';
            }
        };

        $container = new class($devices) {
            public function __construct(private array $devices) {}

            public function get(string $class): object
            {
                if (Sonoff::class !== $class) {
                    throw new \InvalidArgumentException('Unexpected class requested');
                }

                return new class($this->devices) {
                    public function __construct(private array $devices) {}

                    public function getDevices(): array
                    {
                        return $this->devices;
                    }
                };
            }
        };

        ob_start();

        include __DIR__.'/../../pages/start.php';
        $output = ob_get_clean();

        self::assertIsString($output);
        self::assertStringContainsString('desk-lamp', $output);
        self::assertStringNotContainsString('hidden-lamp', $output);
    }
}
