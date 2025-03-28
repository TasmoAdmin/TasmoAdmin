<?php

namespace TasmoAdmin;

use Symfony\Component\Filesystem\Filesystem;

class DeviceRepository
{
    private const CSV_ESCAPE = '\\';

    private string $file;

    private string $tmpDir;

    private Filesystem $filesystem;

    private array $allowedUpdateFields = [
        'id',
        'names',
        'position',
        'ip',
        'username',
        'password',
        'img',
        'position',
        'device_all_off',
        'device_protect_on',
        'device_protect_off',
    ];

    public function __construct(string $file, string $tmpDir)
    {
        $this->file = $file;
        $this->tmpDir = $tmpDir;
        $this->filesystem = new Filesystem();
        $this->createFile();
    }

    public function addDevice(array $request): void
    {
        $deviceUsername = $request['device_username'] ?? '';
        $devicePassword = $request['device_password'] ?? '';
        $this->addDevices([$request], $deviceUsername, $devicePassword);
    }

    public function addDevices(array $devices, string $deviceUsername, string $devicePassword): void
    {
        $handle = fopen($this->file, 'a');
        $nextId = $this->getNextId();
        foreach ($devices as $device) {
            $deviceHolder = [];
            $deviceHolder[0] = $nextId++;
            $deviceHolder[1] = implode('|', $device['device_name'] ?? []);
            $deviceHolder[2] = $device['device_ip'] ?? '';
            $deviceHolder[3] = $deviceUsername;
            $deviceHolder[4] = $devicePassword;
            $deviceHolder[5] = $device['device_img'] ?? 'bulb_1';
            $deviceHolder[6] = $device['device_position'] ?? '';
            $deviceHolder[7] = $device['device_all_off'] ?? 1;
            $deviceHolder[8] = $device['device_protect_on'] ?? 0;
            $deviceHolder[9] = $device['device_protect_off'] ?? 0;
            $deviceHolder[10] = $device['is_updatable'] ?? 1;
            $deviceHolder[11] = $device['device_port'] ?? Device::DEFAULT_PORT;

            fputcsv($handle, $deviceHolder, escape: self::CSV_ESCAPE);
        }

        fclose($handle);
    }

    public function getDeviceById(int $id): ?Device
    {
        $device = null;
        $file = fopen($this->file, 'r');
        while (($line = fgetcsv($file, escape: self::CSV_ESCAPE)) !== false) {
            if ($line[0] == $id) {
                $device = $this->createDeviceObject($line);

                break;
            }
        }

        fclose($file);

        return $device;
    }

    /**
     * @return Device[]
     */
    public function getDevices(): array
    {
        $devices = [];
        $file = fopen($this->file, 'r');
        while (($line = fgetcsv($file, escape: self::CSV_ESCAPE)) !== false) {
            $devices[] = $this->createDeviceObject($line);
        }
        fclose($file);

        return $devices;
    }

    public function getDevicesByIds(array $ids): array
    {
        $devices = $this->getDevices();

        foreach ($devices as $index => $device) {
            if (!in_array($device->id, $ids, false)) {
                unset($devices[$index]);
            }
        }

        return array_values($devices);
    }

    public function setDeviceValue(int $id, string $field, $value = null): ?Device
    {
        $device = $this->getDeviceById($id);
        if (null === $device) {
            return null;
        }

        if (!in_array($field, $this->allowedUpdateFields, true)) {
            return null;
        }

        $device->{$field} = $value;

        return $this->updateDevice($device);
    }

    public function removeDevice(int $id): void
    {
        $this->removeDevices([$id]);
    }

    public function removeDevices(array $ids): void
    {
        $tempFile = $this->filesystem->tempnam($this->tmpDir, 'tmp');

        if (!$input = fopen($this->file, 'r')) {
            exit(__('ERROR_CANNOT_READ_CSV_FILE', 'DEVICE_ACTIONS', ['csvFilePath' => _CSVFILE_]));
        }
        if (!$output = fopen($tempFile, 'w')) {
            exit(__('ERROR_CANNOT_CREATE_TMP_FILE', 'DEVICE_ACTIONS', ['tmpFilePath' => $tempFile]));
        }

        while (($data = fgetcsv($input, escape: self::CSV_ESCAPE)) !== false) {
            if (in_array($data[0], $ids)) {
                continue;
            }
            fputcsv($output, $data, escape: self::CSV_ESCAPE);
        }

        fclose($input);
        fclose($output);
        $this->filesystem->rename($tempFile, $this->file, true);
    }

    public function updateDevice(Device $device): ?Device
    {
        if (empty($device->id)) {
            return null;
        }
        $deviceArr[0] = $device->id;
        $deviceArr[1] = implode('|', !empty($device->names) ? $device->names : []);
        $deviceArr[2] = !empty($device->ip) ? $device->ip : '';
        $deviceArr[3] = !empty($device->username) ? $device->username : '';
        $deviceArr[4] = !empty($device->password) ? $device->password : '';
        $deviceArr[5] = !empty($device->img) ? $device->img : '';
        $deviceArr[6] = !empty($device->position) ? $device->position : '';
        $deviceArr[7] = !empty($device->deviceAllOff) ? $device->deviceAllOff : 0;
        $deviceArr[8] = !empty($device->deviceProtectionOn) ? $device->deviceProtectionOn : 0;
        $deviceArr[9] = !empty($device->deviceProtectionOff) ? $device->deviceProtectionOff : 0;
        $deviceArr[10] = !empty($device->isUpdatable) ? $device->isUpdatable : 0;
        $deviceArr[11] = $device->port;

        foreach ($deviceArr as $key => $field) {
            $deviceArr[$key] = trim($field);
        }

        $tempFile = $this->filesystem->tempnam($this->tmpDir, 'tmp');

        if (!$input = fopen($this->file, 'r')) {
            exit(__('ERROR_CANNOT_READ_CSV_FILE', 'DEVICE_ACTIONS', ['csvFilePath' => _CSVFILE_]));
        }
        if (!$output = fopen($tempFile, 'w')) {
            exit(__('ERROR_CANNOT_CREATE_TMP_FILE', 'DEVICE_ACTIONS', ['tmpFilePath' => $tempFile]));
        }

        while (($data = fgetcsv($input, escape: self::CSV_ESCAPE)) !== false) {
            if ($data[0] == $deviceArr[0]) {
                $data = $deviceArr;
            }
            fputcsv($output, $data, escape: self::CSV_ESCAPE);
        }

        fclose($input);
        fclose($output);
        $this->filesystem->rename($tempFile, $this->file, true);

        return $this->createDeviceObject($deviceArr);
    }

    private function createDeviceObject(array $deviceLine): ?Device
    {
        return DeviceFactory::fromArray($deviceLine);
    }

    private function getNextId(): int
    {
        $id = 0;
        foreach ($this->getDevices() as $device) {
            $id = max($id, $device->id);
        }

        return $id + 1;
    }

    private function createFile(): void
    {
        if (!$this->filesystem->exists($this->file)) {
            $this->filesystem->touch($this->file);
        }
    }
}
