<?php

namespace TasmoAdmin\Update;

class AutoFirmwareResult
{
    private ?string $minimalFirmwareUrl;

    private string $firmwareUrl;

    private string $tagName;

    private \DateTime $publishedAt;

    public function __construct(string $firmwareUrl, ?string $minimalFirmwareUrl, string $tagName, \DateTime $publishedAt)
    {
        $this->firmwareUrl = $firmwareUrl;
        $this->minimalFirmwareUrl = $minimalFirmwareUrl;
        $this->tagName = $tagName;
        $this->publishedAt = $publishedAt;
    }

    public function getFirmwareUrl(): string
    {
        return $this->firmwareUrl;
    }

    public function hasMinimalFirmware(): bool
    {
        return null !== $this->minimalFirmwareUrl;
    }

    public function getMinimalFirmwareUrl(): ?string
    {
        return $this->minimalFirmwareUrl;
    }

    public function getTagName(): string
    {
        return $this->tagName;
    }

    public function getPublishedAt(): \DateTime
    {
        return $this->publishedAt;
    }
}
