<?php

namespace TasmoAdmin;

use stdClass;
use Symfony\Component\Filesystem\Filesystem;

class DeviceRepository
{
    private string $file;

    private Filesystem $filesystem;

    private array $allowedUpdateFields = [
        'id',
        'names',
    ];

    public function __construct(string $file)
    {
        $this->file = $file;
        $this->filesystem = new Filesystem();
    }

    public function getDeviceById(string $id): ?stdClass
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

    public function setDeviceValue(string $id, $field = NULL, $value = NULL): ?stdClass
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

    private function updateDevice(stdClass $device): ?stdClass
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

        foreach ($deviceArr as $key => $field) {
            if (is_array($field)) {
                foreach ($field as $subkey => $subfield) {
                    $deviceArr[$key][$field][$subkey] = trim($subfield);
                }
            }
            else {

                $deviceArr[$key] = trim($field);
            }
        }

        $tempFile = $this->filesystem->tempnam(_TMPDIR_, 'tmp');

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
        $this->filesystem->rename($tempFile, $this->file);

        return $this->createDeviceObject($deviceArr);
    }

    private function createDeviceObject(array $deviceLine): ?stdClass
    {
        return Device::fromLine($deviceLine);
    }
}
