<?php

namespace TasmoAdmin\Helper;

use DateTime;

class TasmoFirmwareResult
{
    private string $version;

    private DateTime $publishDate;

    private array $firmares;


    public function __construct(string $version, DateTime $publishDate, array $firmares)
    {
        $this->version = $version;
        $this->publishDate = $publishDate;
        $this->firmares = $firmares;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getPublishDate(): DateTime
    {
        return $this->publishDate;
    }

    public function getFirmares(): array
    {
        return $this->firmares;
    }
}
