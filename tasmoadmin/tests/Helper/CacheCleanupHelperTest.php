<?php

namespace Tests\TasmoAdmin\Helper;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use TasmoAdmin\Helper\CacheCleanupHelper;

class CacheCleanupHelperTest extends TestCase
{
    public function testCleanTargetsRemovesTemporarySessionAndLanguageCacheFiles(): void
    {
        $filesystem = vfsStream::setup('tmp', null, [
            'sessions' => [
                'session-one' => 'abc',
                '.empty' => '',
            ],
            'cache' => [
                'i18n' => [
                    'json_i18n_en.cache.json' => '{}',
                    'json_i18n_de.cache.json' => '{}',
                ],
            ],
            'keep' => [
                'readme.txt' => 'keep',
            ],
        ]);

        $result = CacheCleanupHelper::cleanTargets($filesystem->url(), ['sessions', 'i18n']);

        self::assertSame(['sessions' => 1, 'i18n' => 2], $result);
        self::assertFileDoesNotExist($filesystem->url().'/sessions/session-one');
        self::assertFileExists($filesystem->url().'/sessions/.empty');
        self::assertFileDoesNotExist($filesystem->url().'/cache/i18n/json_i18n_en.cache.json');
        self::assertFileDoesNotExist($filesystem->url().'/cache/i18n/json_i18n_de.cache.json');
        self::assertFileExists($filesystem->url().'/keep/readme.txt');
    }

    public function testCleanTargetsHandlesMissingFolders(): void
    {
        $filesystem = vfsStream::setup('tmp');

        $result = CacheCleanupHelper::cleanTargets($filesystem->url(), ['sessions', 'i18n']);

        self::assertSame(['sessions' => 0, 'i18n' => 0], $result);
    }
}
