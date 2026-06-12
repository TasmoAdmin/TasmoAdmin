<?php

namespace Tests\TasmoAdmin\Backup;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use TasmoAdmin\Backup\BackupHelper;
use TasmoAdmin\Backup\BackupResult;
use TasmoAdmin\Backup\BackupResults;
use TasmoAdmin\Device;
use TasmoAdmin\DeviceRepository;
use TasmoAdmin\Sonoff;

class BackupHelperTest extends TestCase
{
    private string $tempDir;

    private string $backupPath;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir().'/tasmoadmin-backup-helper-'.bin2hex(random_bytes(6));
        $this->backupPath = $this->tempDir.'/backups/';
        mkdir($this->backupPath, 0o755, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
    }

    public function testBackupCreatesZipAndSuccessfulResults(): void
    {
        file_put_contents($this->backupPath.'stale.txt', 'old');
        $device = new Device(1, ['socket-1'], '192.168.1.10', '', '', 'img');

        $repository = $this->createMock(DeviceRepository::class);
        $repository->expects(self::once())
            ->method('getDeviceById')
            ->with(1)
            ->willReturn($device)
        ;

        $backupDump = $this->tempDir.'/device-1.dmp';
        file_put_contents($backupDump, 'backup');

        $sonoff = $this->createMock(Sonoff::class);
        $sonoff->expects(self::once())
            ->method('backup')
            ->with($device, $this->backupPath)
            ->willReturn($backupDump)
        ;

        $helper = new BackupHelper($repository, $sonoff, $this->backupPath);

        $results = $helper->backup([1]);

        self::assertTrue($results->successful());
        self::assertSame([], $results->getFailures());
        self::assertFileDoesNotExist($this->backupPath.'stale.txt');
        self::assertFileExists($helper->getBackupZipPath());

        $zip = new \ZipArchive();
        self::assertTrue($zip->open($helper->getBackupZipPath()));
        self::assertSame('backup', $zip->getFromName('device-1.dmp'));
        $zip->close();
    }

    public function testBackupCollectsConnectionFailures(): void
    {
        $device = new Device(2, ['socket-2'], '192.168.1.20', '', '', 'img');
        $request = new Request('GET', 'http://192.168.1.20/dl');
        $exception = new ConnectException('offline', $request);

        $repository = $this->createMock(DeviceRepository::class);
        $repository->expects(self::once())
            ->method('getDeviceById')
            ->with(2)
            ->willReturn($device)
        ;

        $sonoff = $this->createMock(Sonoff::class);
        $sonoff->expects(self::once())
            ->method('backup')
            ->with($device, $this->backupPath)
            ->willThrowException($exception)
        ;

        $helper = new BackupHelper($repository, $sonoff, $this->backupPath);

        $results = $helper->backup([2]);
        $failures = $results->getFailures();

        self::assertFalse($results->successful());
        self::assertCount(1, $failures);
        self::assertSame($device, $failures[0]->getDevice());
        self::assertFalse($failures[0]->isSuccessful());
        self::assertSame('offline', $failures[0]->getFailureReason());
    }

    public function testBackupResultsHelpersExposeSuccessAndFailures(): void
    {
        $device = new Device(3, ['socket-3'], '192.168.1.30', '', '', 'img');
        $success = new BackupResult($device, true);
        $failure = new BackupResult($device, false, 'error');
        $results = new BackupResults([$success, $failure]);

        self::assertFalse($results->successful());
        self::assertSame([$failure], $results->getFailures());
    }

    public function testBackupSkipsUnknownDeviceIdsAndStillCreatesEmptyZip(): void
    {
        file_put_contents($this->backupPath.'stale.txt', 'old');

        $repository = $this->createMock(DeviceRepository::class);
        $repository->expects(self::once())
            ->method('getDeviceById')
            ->with(99)
            ->willReturn(null)
        ;

        $sonoff = $this->createMock(Sonoff::class);
        $sonoff->expects(self::never())->method('backup');

        $helper = new BackupHelper($repository, $sonoff, $this->backupPath);

        $results = $helper->backup([99]);

        self::assertTrue($results->successful());
        self::assertSame([], $results->getFailures());
        self::assertFileDoesNotExist($this->backupPath.'stale.txt');
        self::assertDirectoryExists($this->backupPath);
        self::assertFileDoesNotExist($helper->getBackupZipPath());
    }

    public function testBackupResultsHelpersTreatEmptyResultSetAsSuccessful(): void
    {
        $results = new BackupResults([]);

        self::assertTrue($results->successful());
        self::assertSame([], $results->getFailures());
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = array_diff(scandir($directory), ['.', '..']);
        foreach ($items as $item) {
            $path = $directory.'/'.$item;
            if (is_dir($path) && !is_link($path)) {
                $this->removeDirectory($path);

                continue;
            }

            unlink($path);
        }

        rmdir($directory);
    }
}
