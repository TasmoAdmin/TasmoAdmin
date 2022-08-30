<?php

namespace TasmoAdmin\Update;

use DateTime;

class AutoFirmwareResult
{
    private string $minimalFirmwareUrl;

    private string $firmwareUrl;

    private string $tagName;

    private DateTime $publishedAt;

    public function __construct(string $minimalFirmwareUrl, string $firmwareUrl, string $tagName, DateTime $publishedAt)
    {
        $this->minimalFirmwareUrl = $minimalFirmwareUrl;
        $this->firmwareUrl = $firmwareUrl;
        $this->tagName = $tagName;
        $this->publishedAt = $publishedAt;
    }

    public function getMinimalFirmwareUrl(): string
    {
        return $this->minimalFirmwareUrl;
    }

    public function getFirmwareUrl(): string
    {
        return $this->firmwareUrl;
    }

    public function getTagName(): string
    {
        return $this->tagName;
    }

    public function getPublishedAt(): DateTime
    {
        return $this->publishedAt;
    }
}
