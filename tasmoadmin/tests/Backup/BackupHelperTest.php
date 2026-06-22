<?php

namespace Tests\TasmoAdmin\Backup;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use TasmoAdmin\Backup\BackupHelper;
use TasmoAdmin\Backup\BackupResult;
use TasmoAdmin\Backup\BackupResults;
use TasmoAdmin\Config;
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

    public function testRestoreUploadsDumpAndDispatchesWebGetConfig(): void
    {
        $device = new Device(7, ['socket-7'], '192.168.1.70', '', '', 'img');
        $upload = $this->createUploadedBackup('restore.dmp', 'backup');

        $repository = $this->createMock(DeviceRepository::class);
        $repository->expects(self::once())
            ->method('getDeviceById')
            ->with(7)
            ->willReturn($device)
        ;

        $sonoff = $this->createMock(Sonoff::class);
        $sonoff->expects(self::once())
            ->method('restore')
            ->with(
                $device,
                self::callback(static function (string $url): bool {
                    return str_starts_with($url, 'http://192.168.1.1:8080/_BASEURL_/actions?downloadRestore=');
                })
            )
            ->willReturn((object) ['WebGetConfig' => 'Started'])
        ;

        $helper = new BackupHelper(
            $repository,
            $sonoff,
            $this->backupPath,
            $this->createConfig(),
            '/_BASEURL_/'
        );

        $results = $helper->restore([7], $upload);

        self::assertTrue($results->successful());
        self::assertSame([], $results->getFailures());
        self::assertCount(1, glob($this->tempDir.'/restore/*.dmp'));
    }

    public function testRestoreRejectsMultipleDevices(): void
    {
        $helper = new BackupHelper(
            $this->createMock(DeviceRepository::class),
            $this->createMock(Sonoff::class),
            $this->backupPath,
            $this->createConfig(),
            '/_BASEURL_/'
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('BACKUP_RESTORE_SINGLE_DEVICE_ONLY: ');

        $helper->restore([1, 2], $this->createUploadedBackup('restore.dmp', 'backup'));
    }

    public function testRestoreRejectsFilesWithoutDumpExtension(): void
    {
        $helper = new BackupHelper(
            $this->createMock(DeviceRepository::class),
            $this->createMock(Sonoff::class),
            $this->backupPath,
            $this->createConfig(),
            '/_BASEURL_/'
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('BACKUP_RESTORE_FILE_WRONG_FORMAT: ');

        $helper->restore([1], $this->createUploadedBackup('restore.zip', 'backup'));
    }

    public function testRestoreTurnsDeviceErrorsIntoFailureResults(): void
    {
        $device = new Device(9, ['socket-9'], '192.168.1.90', '', '', 'img');
        $upload = $this->createUploadedBackup('restore.dmp', 'backup');

        $repository = $this->createMock(DeviceRepository::class);
        $repository->expects(self::once())
            ->method('getDeviceById')
            ->with(9)
            ->willReturn($device)
        ;

        $sonoff = $this->createMock(Sonoff::class);
        $sonoff->expects(self::once())
            ->method('restore')
            ->willReturn((object) ['ERROR' => 'Restore failed'])
        ;

        $helper = new BackupHelper(
            $repository,
            $sonoff,
            $this->backupPath,
            $this->createConfig(),
            '/_BASEURL_/'
        );

        $results = $helper->restore([9], $upload);
        $failures = $results->getFailures();

        self::assertFalse($results->successful());
        self::assertCount(1, $failures);
        self::assertSame('Restore failed', $failures[0]->getFailureReason());
    }

    public function testDeleteRestoreFileRemovesExistingDump(): void
    {
        $restoreToken = 'abcdefabcdefabcdefabcdef';
        $restoreDir = $this->tempDir.'/restore/';
        mkdir($restoreDir, 0o755, true);
        $restorePath = $restoreDir.$restoreToken.'.dmp';
        file_put_contents($restorePath, 'backup');

        $helper = new BackupHelper(
            $this->createMock(DeviceRepository::class),
            $this->createMock(Sonoff::class),
            $this->backupPath,
            $this->createConfig(),
            '/_BASEURL_/'
        );

        self::assertSame($restorePath, $helper->getRestoreFilePath($restoreToken));

        $helper->deleteRestoreFile($restoreToken);

        self::assertNull($helper->getRestoreFilePath($restoreToken));
        self::assertFileDoesNotExist($restorePath);
    }

    private function createUploadedBackup(string $filename, string $contents): array
    {
        $tmpFile = tempnam($this->tempDir, 'restore-upload-');
        file_put_contents($tmpFile, $contents);

        return [
            'name' => $filename,
            'tmp_name' => $tmpFile,
            'error' => UPLOAD_ERR_OK,
            'size' => strlen($contents),
        ];
    }

    private function createConfig(): Config
    {
        $dataDir = $this->tempDir.'/config-data/';
        $appRoot = $this->tempDir.'/app-root/';
        mkdir($dataDir, 0o755, true);
        mkdir($appRoot, 0o755, true);

        $config = new Config($dataDir, $appRoot);
        $config->write('ota_server_ip', '192.168.1.1');
        $config->write('ota_server_port', '8080');

        return $config;
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
