<?php

namespace TasmoAdmin\Helper;

class FirmwareFolderHelper
{
    private const IGNORED_FILES = [
        '.empty',
        '.htaccess',
    ];

    public static function clean(string $firmwareFolder): void
    {
        $files = scandir($firmwareFolder);
        foreach ($files as $file) {
            $file = $firmwareFolder . $file;
            if (is_file($file) && !in_array(basename($file), self::IGNORED_FILES)) {
                unlink($file);
            }
        }
    }
}
