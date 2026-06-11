<?php

declare(strict_types=1);

namespace Tests\TasmoAdmin\Page;

use PHPUnit\Framework\TestCase;
use TasmoAdmin\Backup\BackupHelper;
use TasmoAdmin\Backup\BackupResult;
use TasmoAdmin\Backup\BackupResults;
use TasmoAdmin\Device;
use TasmoAdmin\Sonoff;

class DevicesPageBackupWorkflowTest extends TestCase
{
    protected function tearDown(): void
    {
        $_POST = [];
    }

    public function testDevicesPageRendersBackupAsBatchAction(): void
    {
        $devices = [$this->createDevice(1, 'Desk Lamp')];
        $output = $this->renderDevicesPage($devices);

        self::assertStringContainsString("<option value='backup'>", $output);
        self::assertStringContainsString('batchActionSelect', $output);
    }

    public function testDevicesPageShowsInlineBackupResultAndFailures(): void
    {
        $_POST = [
            'batch_action' => 'backup',
            'device_ids' => ['1', '2'],
        ];

        $devices = [
            $this->createDevice(1, 'Desk Lamp'),
            $this->createDevice(2, 'Shelf Plug'),
        ];
        $results = new BackupResults([
            new BackupResult($devices[0], true),
            new BackupResult($devices[1], false, 'Connection timed out'),
        ]);

        $output = $this->renderDevicesPage($devices, $results);

        self::assertStringContainsString('BACKUP_BACKUP_FINISHED:', $output);
        self::assertStringContainsString('/actions?downloadBackup', $output);
        self::assertStringContainsString('BACKUP_BACKUP_FAILED:', $output);
        self::assertStringContainsString('Shelf Plug: Connection timed out', $output);
    }

    private function renderDevicesPage(array $devices, ?BackupResults $backupResults = null): string
    {
        if (!defined('_BASEURL_')) {
            define('_BASEURL_', '/');
        }
        if (!defined('_RESOURCESURL_')) {
            define('_RESOURCESURL_', '/resources/');
        }

        $Config = new class {
            public function read(string $key): string
            {
                return match ($key) {
                    'show_search' => '1',
                    default => '',
                };
            }
        };

        $urlHelper = new class {
            public function js(string $path): string
            {
                return '/js/'.$path.'.js';
            }
        };

        $container = new class($devices, $backupResults) {
            public function __construct(private array $devices, private ?BackupResults $backupResults) {}

            public function get(string $class): object
            {
                return match ($class) {
                    Sonoff::class => new class($this->devices) {
                        public function __construct(private array $devices) {}

                        public function getDevices(): array
                        {
                            return $this->devices;
                        }
                    },
                    BackupHelper::class => new class($this->backupResults) {
                        public function __construct(private ?BackupResults $backupResults) {}

                        public function backup(array $deviceIds): BackupResults
                        {
                            return $this->backupResults ?? new BackupResults([]);
                        }
                    },
                    default => throw new \InvalidArgumentException('Unexpected class '.$class),
                };
            }
        };

        ob_start();

        try {
            include __DIR__.'/../../pages/devices.php';

            return (string) ob_get_clean();
        } catch (\Throwable $exception) {
            ob_end_clean();

            throw $exception;
        }
    }

    private function createDevice(int $id, string $name): Device
    {
        return new Device($id, [$name], '192.168.1.'.$id, '', '');
    }
}
