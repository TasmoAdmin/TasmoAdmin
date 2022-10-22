<?php

namespace TasmoAdmin\Update;

use DateTime;

class AutoFirmwareResult
{
    private string $firmwareUrl;

    private string $tagName;

    private DateTime $publishedAt;

    public function __construct(string $firmwareUrl, string $tagName, DateTime $publishedAt)
    {
        $this->firmwareUrl = $firmwareUrl;
        $this->tagName = $tagName;
        $this->publishedAt = $publishedAt;
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
