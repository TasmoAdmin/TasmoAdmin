<?php

declare(strict_types=1);

namespace Tests\TasmoAdmin\Page;

use PHPUnit\Framework\TestCase;
use TasmoAdmin\Helper\UrlHelper;
use TasmoAdmin\Helper\ViewHelper;

class HeaderDevicesNavigationTest extends TestCase
{
    public function testHeaderDoesNotRenderStandaloneBackupEntryInDevicesMenu(): void
    {
        if (!defined('_BASEURL_')) {
            define('_BASEURL_', '/');
        }
        if (!defined('_RESOURCESURL_')) {
            define('_RESOURCESURL_', '/resources/');
        }
        if (!defined('_RESOURCESDIR_')) {
            define('_RESOURCESDIR_', dirname(__DIR__, 2).'/resources/');
        }

        $lang = 'en';
        $page = 'devices';
        $loggedin = true;
        $docker = false;

        $Config = new class {
            public function read(string $key): string
            {
                return match ($key) {
                    'homepage' => 'devices',
                    'nightmode' => 'disable',
                    default => '0',
                };
            }

            public function getRequestConcurrency(): int
            {
                return 4;
            }
        };

        $container = new class {
            public function get(string $class): object
            {
                return match ($class) {
                    UrlHelper::class => new class {
                        public function js(string $path): string
                        {
                            return '/js/'.$path.'.js';
                        }

                        public function style(string $path): string
                        {
                            return '/css/'.$path.'.css';
                        }
                    },
                    ViewHelper::class => new class {
                        public function getValue(bool $value): string
                        {
                            return $value ? 'true' : 'false';
                        }

                        public function getNightMode(string $hour): string
                        {
                            return 'daymode';
                        }
                    },
                    default => throw new \InvalidArgumentException('Unexpected class '.$class),
                };
            }
        };

        ob_start();

        try {
            include __DIR__.'/../../includes/header.php';
            $output = (string) ob_get_clean();
        } catch (\Throwable $exception) {
            ob_end_clean();

            throw $exception;
        }

        self::assertStringNotContainsString('NAVI_DEVICES_BACKUP:', $output);
        self::assertStringContainsString('NAVI_DEVICE_LIST:', $output);
        self::assertStringContainsString('NAVI_DEVICES_AUTOSCAN:', $output);
    }
}
