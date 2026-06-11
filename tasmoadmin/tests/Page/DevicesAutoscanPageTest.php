<?php

declare(strict_types=1);

namespace Tests\TasmoAdmin\Page;

use PHPUnit\Framework\TestCase;
use TasmoAdmin\Config;
use TasmoAdmin\Sonoff;

class DevicesAutoscanPageTest extends TestCase
{
    public function testAutoscanPageRendersNetworkAndMqttTabs(): void
    {
        if (!defined('_BASEURL_')) {
            define('_BASEURL_', '/');
        }

        $_REQUEST = [];
        $title = 'AutoScan';
        $container = new class {
            public function get(string $class): object
            {
                return match ($class) {
                    Sonoff::class => new class {
                        public function getDevices(): array
                        {
                            return [];
                        }
                    },
                    Config::class => new class {
                        public function readAll(): array
                        {
                            return [
                                'mqtt_discovery_password' => 'secret',
                            ];
                        }

                        public function read(string $key): string
                        {
                            return match ($key) {
                                'scan_from_ip' => '192.168.1.2',
                                'scan_to_ip' => '192.168.1.254',
                                'additional_scan_ranges' => '',
                                'port' => '80',
                                'mqtt_discovery_host' => 'broker.local',
                                'mqtt_discovery_port' => '1883',
                                'mqtt_discovery_username' => 'mqtt',
                                'mqtt_discovery_password' => 'secret',
                                'mqtt_discovery_cmnd_prefix' => 'cmnd',
                                'mqtt_discovery_stat_prefix' => 'stat',
                                'mqtt_discovery_tele_prefix' => 'tele',
                                'mqtt_discovery_subscriptions' => 'tele/+/LWT',
                                'mqtt_discovery_timeout_seconds' => '5',
                                default => '',
                            };
                        }
                    },
                    default => throw new \InvalidArgumentException('Unexpected class '.$class),
                };
            }
        };

        ob_start();

        try {
            include __DIR__.'/../../pages/devices_autoscan.php';
            $output = (string) ob_get_clean();
        } catch (\Throwable $exception) {
            ob_end_clean();

            throw $exception;
        }

        self::assertStringContainsString('NETWORK', $output);
        self::assertStringContainsString('MQTT', $output);
        self::assertStringContainsString('name="scan_mode"', $output);
    }

    public function testAutoscanPageDoesNotRenderStoredMqttPassword(): void
    {
        if (!defined('_BASEURL_')) {
            define('_BASEURL_', '/');
        }

        $_REQUEST = ['scan_mode' => 'mqtt'];
        $title = 'AutoScan';
        $container = new class {
            public function get(string $class): object
            {
                return match ($class) {
                    Sonoff::class => new class {
                        public function getDevices(): array
                        {
                            return [];
                        }
                    },
                    Config::class => new class {
                        public function readAll(): array
                        {
                            return [
                                'mqtt_discovery_password' => 'super-secret',
                            ];
                        }

                        public function read(string $key): string
                        {
                            return match ($key) {
                                'scan_from_ip' => '192.168.1.2',
                                'scan_to_ip' => '192.168.1.254',
                                'additional_scan_ranges' => '',
                                'port' => '80',
                                'mqtt_discovery_host' => 'broker.local',
                                'mqtt_discovery_port' => '1883',
                                'mqtt_discovery_username' => 'mqtt',
                                'mqtt_discovery_password' => 'super-secret',
                                'mqtt_discovery_cmnd_prefix' => 'cmnd',
                                'mqtt_discovery_stat_prefix' => 'stat',
                                'mqtt_discovery_tele_prefix' => 'tele',
                                'mqtt_discovery_subscriptions' => 'tele/+/LWT',
                                'mqtt_discovery_timeout_seconds' => '5',
                                default => '',
                            };
                        }
                    },
                    default => throw new \InvalidArgumentException('Unexpected class '.$class),
                };
            }
        };

        ob_start();

        try {
            include __DIR__.'/../../pages/devices_autoscan.php';
            $output = (string) ob_get_clean();
        } catch (\Throwable $exception) {
            ob_end_clean();

            throw $exception;
        }

        self::assertStringNotContainsString('super-secret', $output);
        self::assertStringContainsString('DEVICES_AUTOSCAN_MQTT_DISCOVERY_PASSWORD_STORED:', $output);
        self::assertStringContainsString('value=\'\'', $output);
    }
}
