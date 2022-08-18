<?php

namespace TasmoAdmin\Helper;

class TasmoFirmwareResult
{
    private string $version;

    private array $firmares;


    public function __construct(string $version, array $firmares)
    {
        $this->version = $version;
        $this->firmares = $firmares;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getFirmares(): array
    {
        return $this->firmares;
    }


}
