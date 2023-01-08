<?php

namespace TasmoAdmin\Backup;

use GuzzleHttp\Exception\ConnectException;
use Symfony\Component\Filesystem\Filesystem;
use TasmoAdmin\Backup\BackupResult;
use TasmoAdmin\Backup\BackupResults;
use TasmoAdmin\DeviceRepository;
use TasmoAdmin\Sonoff;
use ZipArchive;

class BackupHelper
{
    private DeviceRepository $deviceRepository;

    private Sonoff $sonoff;
    private string $backupPath;

    private Filesystem $filesystem;


    public function __construct(DeviceRepository $deviceRepository, Sonoff $sonoff, string $backupPath)
    {
        $this->deviceRepository = $deviceRepository;
        $this->sonoff = $sonoff;
        $this->backupPath = $backupPath;
        $this->filesystem = new Filesystem();
    }

    public function backup(array $deviceIds): BackupResults
    {
        $this->createCleanBackupDir();
        $files = [];
        $results = [];
        foreach ($deviceIds as $deviceId) {
            $device = $this->deviceRepository->getDeviceById($deviceId);
            if ($device === null) {
                continue;
            }

            try {
                $files[] = $this->sonoff->backup($device, $this->backupPath);
                $results[] = new BackupResult($device, true);
            } catch(ConnectException $exception) {
                // Failed to download
                $results[] = new BackupResult($device, true);
            }
        }

        return new BackupResults($this->createZip($files), $results);
    }

    private function createZip(array $files): string
    {
        $zipFilePath = $this->backupPath . 'backup.zip';
        if (file_exists($zipFilePath)) {
            unlink($zipFilePath);
        }

        $zip = new ZipArchive();
        $zip->open($zipFilePath, ZipArchive::CREATE);
        foreach ($files as $file) {
            $zip->addFile($file, basename($file));
        }

        $zip->close();

        return $zipFilePath;
    }


    private function createCleanBackupDir(): void
    {
        if ($this->filesystem->exists($this->backupPath)) {
            $this->filesystem->remove($this->backupPath);
        }

        $this->filesystem->mkdir($this->backupPath);
    }
}
