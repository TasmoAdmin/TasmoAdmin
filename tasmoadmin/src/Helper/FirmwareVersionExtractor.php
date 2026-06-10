<?php

namespace TasmoAdmin\Helper;

class FirmwareVersionExtractor
{
    public static function fromFilename(string $filename): ?string
    {
        if (!preg_match('/(?<!\d)(\d+\.\d+\.\d+(?:\.\d+)?)(?!\d)/', $filename, $matches)) {
            return null;
        }

        return $matches[1];
    }
}
