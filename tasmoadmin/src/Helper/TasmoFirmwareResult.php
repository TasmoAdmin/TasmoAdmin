<?php

namespace TasmoAdmin\Helper;

use DateTime;

class TasmoFirmwareResult
{
    private string $version;

    private DateTime $publishDate;

    private array $firmwares;


    public function __construct(string $version, DateTime $publishDate, array $firmwares)
    {
        $this->version = $version;
        $this->publishDate = $publishDate;
        $this->firmwares = $firmwares;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getPublishDate(): DateTime
    {
        return $this->publishDate;
    }

    public function getFirmwares(): array
    {
        return $this->firmwares;
    }
}
