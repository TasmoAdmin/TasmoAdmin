<?php

namespace TasmoAdmin\Helper;

class GzipHelper
{
    public static function unzip(string $gzFile, string $outputFile): string
    {
        $bufferSize = 4096;
        $file = gzopen($gzFile, 'rb');
        $out_file = fopen($outputFile, 'wb');
        while (!gzeof($file)) {
            fwrite($out_file, gzread($file, $bufferSize));
        }

        return $outputFile;
    }
}
