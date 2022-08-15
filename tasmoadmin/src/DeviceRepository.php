<?php

namespace TasmoAdmin;

use Symfony\Component\Filesystem\Filesystem;

class DeviceRepository
{
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
    }

    public function addDevice(array $request): void
    {
        $deviceUsername = $request["device_username"] ?? "";
        $devicePassword = $request["device_password"] ?? "";
        $this->addDevices([$request], $deviceUsername, $devicePassword);
    }

    public function addDevices(array $devices, string $deviceUsername, string $devicePassword): void
    {
        $handle = fopen($this->file, "a");
        foreach ($devices as $device) {
            $fp = file($this->file);
            $deviceHolder = [];
            $deviceHolder[0] = count($fp) + 1;
            $deviceHolder[1] = implode("|", $device["device_name"] ?? []);
            $deviceHolder[2] = $device["device_ip"] ?? "";
            $deviceHolder[3] = $deviceUsername;
            $deviceHolder[4] = $devicePassword;
            $deviceHolder[5] = $device["device_img"] ?? "bulb_1";
            $deviceHolder[6] = $device["device_position"] ?? "";
            $deviceHolder[7] = $device["device_all_off"] ?? 1;
            $deviceHolder[8] = $device["device_protect_on"] ?? 0;
            $deviceHolder[9] = $device["device_protect_off"] ?? 0;

            fputcsv($handle, $deviceHolder);
        }

        fclose($handle);
    }

    public function getDeviceById(string $id): ?Device
    {
        if (empty($id)) {
            return null;
        }

        $device = null;

        $file = fopen($this->file, 'r');
        while (($line = fgetcsv($file)) !== FALSE) {
            if ($line[0] === $id) {
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
        while (($line = fgetcsv($file)) !== FALSE) {
            $devices[] = $this->createDeviceObject($line);
        }
        fclose($file);

        return $devices;
    }

    public function setDeviceValue(string $id, string $field, $value = null): ?Device
    {
        if (empty($id)) {
            return null;
        }

        if (!in_array($field, $this->allowedUpdateFields, true)) {
            return null;
        }

        $device = $this->getDeviceById($id);
        if ($device === null) {
            return null;
        }

        $device->$field = $value;

        return $this->updateDevice($device);
    }

    public function removeDevice(string $id): void
    {
        $tempFile = $this->filesystem->tempnam($this->tmpDir, 'tmp');

        if (!$input = fopen($this->file, 'r')) {
            die(__("ERROR_CANNOT_READ_CSV_FILE", "DEVICE_ACTIONS", ["csvFilePath" => _CSVFILE_]));
        }
        if (!$output = fopen($tempFile, 'w')) {
            die(__("ERROR_CANNOT_CREATE_TMP_FILE", "DEVICE_ACTIONS", ["tmpFilePath" => $tempFile]));
        }

        while (($data = fgetcsv($input)) !== FALSE) {
            if ($data[0] === $id) {
                continue;
            }
            fputcsv($output, $data);
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
        $deviceArr[1] = implode("|", isset($device->names) && !empty($device->names) ? $device->names : []);
        $deviceArr[2] = isset($device->ip) && !empty($device->ip) ? $device->ip : "";
        $deviceArr[3] = isset($device->username) && !empty($device->username) ? $device->username : "";
        $deviceArr[4] = isset($device->password) && !empty($device->password) ? $device->password : "";
        $deviceArr[5] = isset($device->img) && !empty($device->img) ? $device->img : "";
        $deviceArr[6] = isset($device->position) && !empty($device->position) ? $device->position : "";
        $deviceArr[7] = isset($device->deviceAllOff) && !empty($device->deviceAllOff) ? $device->deviceAllOff : 0;
        $deviceArr[8] = isset($device->deviceProtectionOn) && !empty($device->deviceProtectionOn) ? $device->deviceProtectionOn : 0;
        $deviceArr[9] = isset($device->deviceProtectionOff) && !empty($device->deviceProtectionOff) ? $device->deviceProtectionOff : 0;

        foreach ($deviceArr as $key => $field) {
            if (is_array($field)) {
                foreach ($field as $subkey => $subfield) {
                    $deviceArr[$key][$field][$subkey] = trim($subfield);
                }
            } else {

                $deviceArr[$key] = trim($field);
            }
        }

        $tempFile = $this->filesystem->tempnam($this->tmpDir, 'tmp');

        if (!$input = fopen($this->file, 'r')) {
            die(__("ERROR_CANNOT_READ_CSV_FILE", "DEVICE_ACTIONS", ["csvFilePath" => _CSVFILE_]));
        }
        if (!$output = fopen($tempFile, 'w')) {
            die(__("ERROR_CANNOT_CREATE_TMP_FILE", "DEVICE_ACTIONS", ["tmpFilePath" => $tempFile]));
        }

        while (($data = fgetcsv($input)) !== FALSE) {
            if ($data[0] == $deviceArr[0]) {
                $data = $deviceArr;
            }
            fputcsv($output, $data);
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
}
