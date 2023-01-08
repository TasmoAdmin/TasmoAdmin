<?php

namespace TasmoAdmin\Backup;

use TasmoAdmin\Device;

class BackupResult
{
    private Device $device;

    private bool $successful;

    public function __construct(Device $device, bool $successful)
    {
        $this->device = $device;
        $this->successful = $successful;
    }

    public function getDevice(): Device
    {
        return $this->device;
    }

    public function isSuccessful(): bool
    {
        return $this->successful;
    }
}
