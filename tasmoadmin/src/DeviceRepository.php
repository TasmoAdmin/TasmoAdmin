<?php

namespace TasmoAdmin;

use Symfony\Component\Filesystem\Filesystem;

class DeviceRepository
{
    private const CSV_ESCAPE = '\\';

    private string $file;

    private string $tmpDir;

    private Filesystem $filesystem;

    private DevicePasswordCipher $devicePasswordCipher;

    private bool $defaultConfirmDeviceToggles;

    private array $allowedUpdateFields = [
        'id',
        'names',
        'friendlyNames',
        'position',
        'ip',
        'username',
        'password',
        'img',
        'position',
        'device_all_off',
        'device_protect_on',
        'device_protect_off',
        'isUpdatable',
        'port',
        'deviceConfirmToggle',
        'mqttTopic',
    ];

    public function __construct(
        string $file,
        string $tmpDir,
        DevicePasswordCipher $devicePasswordCipher,
        bool $defaultConfirmDeviceToggles = false
    ) {
        $this->file = $file;
        $this->tmpDir = $tmpDir;
        $this->filesystem = new Filesystem();
        $this->devicePasswordCipher = $devicePasswordCipher;
        $this->defaultConfirmDeviceToggles = $defaultConfirmDeviceToggles;
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
            $deviceHolder[4] = $this->encodePasswordForStorage($devicePassword);
            $deviceHolder[5] = $device['device_img'] ?? 'bulb_1';
            $deviceHolder[6] = $device['device_position'] ?? '';
            $deviceHolder[7] = $device['device_all_off'] ?? 1;
            $deviceHolder[8] = $device['device_protect_on'] ?? 0;
            $deviceHolder[9] = $device['device_protect_off'] ?? 0;
            $deviceHolder[10] = $device['is_updatable'] ?? 1;
            $deviceHolder[11] = $device['device_port'] ?? Device::DEFAULT_PORT;
            $deviceHolder[12] = implode('|', $device['device_friendly_name'] ?? ($device['device_name'] ?? []));
            $deviceHolder[13] = $device['device_confirm_toggle'] ?? ($this->defaultConfirmDeviceToggles ? 1 : 0);
            $deviceHolder[14] = trim((string) ($device['device_mqtt_topic'] ?? ''));

            fputcsv($handle, $deviceHolder, escape: self::CSV_ESCAPE);
        }

        fclose($handle);
    }

    public function getDeviceById(int $id): ?Device
    {
        $device = null;
        $storageRows = [];
        $needsRewrite = false;
        $file = $this->openInputFile();
        while (($line = fgetcsv($file, escape: self::CSV_ESCAPE)) !== false) {
            [$storageRow, $rowNeedsRewrite] = $this->prepareStorageRow($line);
            $storageRows[] = $storageRow;
            $needsRewrite = $needsRewrite || $rowNeedsRewrite;

            if ($line[0] == $id) {
                $device = $this->createDeviceObject($storageRow);
            }
        }
        fclose($file);

        if ($needsRewrite) {
            $this->rewriteRows($storageRows);
        }

        return $device;
    }

    /**
     * @return Device[]
     */
    public function getDevices(): array
    {
        $devices = [];
        $storageRows = [];
        $needsRewrite = false;
        $file = $this->openInputFile();
        while (($line = fgetcsv($file, escape: self::CSV_ESCAPE)) !== false) {
            [$storageRow, $rowNeedsRewrite] = $this->prepareStorageRow($line);
            $storageRows[] = $storageRow;
            $needsRewrite = $needsRewrite || $rowNeedsRewrite;
            $devices[] = $this->createDeviceObject($storageRow);
        }
        fclose($file);

        if ($needsRewrite) {
            $this->rewriteRows($storageRows);
        }

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

    /**
     * @return Device[]
     */
    public function getDevicesByMqttTopic(string $mqttTopic): array
    {
        $mqttTopic = trim($mqttTopic);
        if ('' === $mqttTopic) {
            return [];
        }

        $devices = [];
        foreach ($this->getDevices() as $device) {
            if ($device->mqttTopic === $mqttTopic) {
                $devices[] = $device;
            }
        }

        return $devices;
    }

    public function getDeviceByMqttTopic(string $mqttTopic): ?Device
    {
        $devices = $this->getDevicesByMqttTopic($mqttTopic);

        return 1 === count($devices) ? $devices[0] : null;
    }

    public function isMqttTopicAmbiguous(string $mqttTopic): bool
    {
        return count($this->getDevicesByMqttTopic($mqttTopic)) > 1;
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

    public function setDeviceConfirmToggleForAll(bool $enabled): void
    {
        $storageRows = [];
        $input = $this->openInputFile();
        while (($data = fgetcsv($input, escape: self::CSV_ESCAPE)) !== false) {
            [$storageRow] = $this->prepareStorageRow($data);
            $storageRow[13] = $enabled ? 1 : 0;
            $storageRows[] = $storageRow;
        }
        fclose($input);

        $this->rewriteRows($storageRows);
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
        $deviceArr[4] = $this->encodePasswordForStorage($device->password ?? '');
        $deviceArr[5] = !empty($device->img) ? $device->img : '';
        $deviceArr[6] = !empty($device->position) ? $device->position : '';
        $deviceArr[7] = !empty($device->deviceAllOff) ? $device->deviceAllOff : 0;
        $deviceArr[8] = !empty($device->deviceProtectionOn) ? $device->deviceProtectionOn : 0;
        $deviceArr[9] = !empty($device->deviceProtectionOff) ? $device->deviceProtectionOff : 0;
        $deviceArr[10] = !empty($device->isUpdatable) ? $device->isUpdatable : 0;
        $deviceArr[11] = $device->port;
        $deviceArr[12] = implode('|', !empty($device->friendlyNames) ? $device->friendlyNames : $device->names);
        $deviceArr[13] = !empty($device->deviceConfirmToggle) ? $device->deviceConfirmToggle : 0;
        $deviceArr[14] = $device->mqttTopic ?? '';

        foreach ($deviceArr as $key => $field) {
            $deviceArr[$key] = trim($field);
        }

        $tempFile = $this->filesystem->tempnam($this->tmpDir, 'tmp');
        $input = $this->openInputFile();
        $output = $this->openOutputFile($tempFile);

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
        $deviceLine[4] = $this->devicePasswordCipher->decrypt((string) ($deviceLine[4] ?? ''));

        return DeviceFactory::fromArray($deviceLine);
    }

    private function prepareStorageRow(array $deviceLine): array
    {
        $needsRewrite = false;
        $defaults = [
            0 => '',
            1 => '',
            2 => '',
            3 => '',
            4 => '',
            5 => Device::DEFAULT_IMAGE,
            6 => '0',
            7 => '1',
            8 => '0',
            9 => '0',
            10 => '1',
            11 => (string) Device::DEFAULT_PORT,
            12 => '',
            13 => $this->defaultConfirmDeviceToggles ? '1' : '0',
            14 => '',
        ];

        foreach ($defaults as $index => $defaultValue) {
            if (!array_key_exists($index, $deviceLine)) {
                $deviceLine[$index] = $defaultValue;
                $needsRewrite = true;
            }
        }

        ksort($deviceLine);

        $password = (string) ($deviceLine[4] ?? '');
        if ('' === $password || $this->devicePasswordCipher->isRecognizedEncryptedPayload($password)) {
            return [$deviceLine, $needsRewrite];
        }

        $deviceLine[4] = $this->devicePasswordCipher->encrypt($password);

        return [$deviceLine, true];
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

    private function encodePasswordForStorage(string $password): string
    {
        if ('' === $password) {
            return '';
        }

        return $this->devicePasswordCipher->encrypt($password);
    }

    private function rewriteRows(array $rows): void
    {
        $tempFile = $this->filesystem->tempnam($this->tmpDir, 'tmp');
        $output = $this->openOutputFile($tempFile);

        foreach ($rows as $row) {
            fputcsv($output, $row, escape: self::CSV_ESCAPE);
        }

        fclose($output);
        $this->filesystem->rename($tempFile, $this->file, true);
    }

    private function openInputFile()
    {
        $input = fopen($this->file, 'r');
        if (false === $input) {
            $csvFilePath = defined('_CSVFILE_') ? _CSVFILE_ : $this->file;

            exit(__('ERROR_CANNOT_READ_CSV_FILE', 'DEVICE_ACTIONS', ['csvFilePath' => $csvFilePath]));
        }

        return $input;
    }

    private function openOutputFile(string $path)
    {
        $output = fopen($path, 'w');
        if (false === $output) {
            exit(__('ERROR_CANNOT_CREATE_TMP_FILE', 'DEVICE_ACTIONS', ['tmpFilePath' => $path]));
        }

        return $output;
    }
}
