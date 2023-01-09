<?php

namespace TasmoAdmin\Backup;

use TasmoAdmin\Device;

class BackupResult
{
    private Device $device;

    private bool $successful;

    private string $failureReason;

    public function __construct(Device $device, bool $successful, string $failureReason = '')
    {
        $this->device = $device;
        $this->successful = $successful;
        $this->failureReason = $failureReason;
    }

    public function getDevice(): Device
    {
        return $this->device;
    }

    public function isSuccessful(): bool
    {
        return $this->successful;
    }

    public function getFailureReason(): string
    {
        return $this->failureReason;
    }
}
