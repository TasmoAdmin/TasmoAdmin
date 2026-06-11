<?php

namespace TasmoAdmin\Helper;

class CacheCleanupHelper
{
    /**
     * @param list<string> $targets
     *
     * @return array{sessions:int,i18n:int}
     */
    public static function cleanTargets(string $tmpDir, array $targets): array
    {
        $tmpDir = rtrim($tmpDir, '/');
        $result = [
            'sessions' => 0,
            'i18n' => 0,
        ];

        if (in_array('sessions', $targets, true)) {
            $result['sessions'] = self::cleanDirectory($tmpDir.'/sessions', true);
        }

        if (in_array('i18n', $targets, true)) {
            $result['i18n'] = self::cleanDirectory($tmpDir.'/cache/i18n', false);
        }

        return $result;
    }

    private static function cleanDirectory(string $directory, bool $preserveEmptyFile): int
    {
        $removed = 0;

        if (!is_dir($directory)) {
            return 0;
        }

        $files = scandir($directory);

        if (false === $files) {
            return 0;
        }

        foreach ($files as $fileName) {
            if ('.' === $fileName || '..' === $fileName) {
                continue;
            }

            $file = $directory.'/'.$fileName;
            if (!is_file($file)) {
                continue;
            }

            if ($preserveEmptyFile && '.empty' === $fileName) {
                continue;
            }

            if (@unlink($file)) {
                ++$removed;
            }
        }

        return $removed;
    }
}
