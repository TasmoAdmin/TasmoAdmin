<?php

namespace TasmoAdmin\Update;

class AutoFirmwareResult
{
    private string $minimalFirmwareUrl;

    private string $firmwareUrl;

    private string $tagName;

    private string $publishedAt;

    public function __construct(string $minimalFirmwareUrl, string $firmwareUrl, string $tagName, string $publishedAt)
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

    public function getPublishedAt(): string
    {
        return $this->publishedAt;
    }
}
