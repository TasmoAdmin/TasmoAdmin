<?php

namespace TasmoAdmin\Update;

class AutoFirmwareResult
{
    private string $minimalFirmwareUrl;

    private string $firmwareUrl;

    private string $tagName;

    public function __construct(string $minimalFirmwareUrl, string $firmwareUrl, string $tagName)
    {
        $this->minimalFirmwareUrl = $minimalFirmwareUrl;
        $this->firmwareUrl = $firmwareUrl;
        $this->tagName = $tagName;
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
}
