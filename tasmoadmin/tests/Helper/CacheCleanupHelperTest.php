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

    public function testCleanTargetsCanCleanOnlySessionsWithoutTouchingI18n(): void
    {
        $filesystem = vfsStream::setup('tmp', null, [
            'sessions' => [
                'session-one' => 'abc',
                '.empty' => '',
            ],
            'cache' => [
                'i18n' => [
                    'json_i18n_en.cache.json' => '{}',
                ],
            ],
        ]);

        $result = CacheCleanupHelper::cleanTargets($filesystem->url(), ['sessions']);

        self::assertSame(['sessions' => 1, 'i18n' => 0], $result);
        self::assertFileDoesNotExist($filesystem->url().'/sessions/session-one');
        self::assertFileExists($filesystem->url().'/sessions/.empty');
        self::assertFileExists($filesystem->url().'/cache/i18n/json_i18n_en.cache.json');
    }

    public function testCleanTargetsIgnoresDirectoriesInsideCleanupTargets(): void
    {
        $filesystem = vfsStream::setup('tmp', null, [
            'sessions' => [
                '.empty' => '',
                'nested' => [
                    'session-two' => 'abc',
                ],
            ],
            'cache' => [
                'i18n' => [
                    'nested' => [
                        'json_i18n_en.cache.json' => '{}',
                    ],
                ],
            ],
        ]);

        $result = CacheCleanupHelper::cleanTargets($filesystem->url(), ['sessions', 'i18n']);

        self::assertSame(['sessions' => 0, 'i18n' => 0], $result);
        self::assertFileExists($filesystem->url().'/sessions/nested/session-two');
        self::assertFileExists($filesystem->url().'/cache/i18n/nested/json_i18n_en.cache.json');
    }

    public function testCleanTargetsReturnsZeroWhenSessionDirectoryCannotBeScanned(): void
    {
        $tmpDir = sys_get_temp_dir().'/cache-cleanup-helper-'.bin2hex(random_bytes(8));
        $sessionsDir = $tmpDir.'/sessions';
        mkdir($tmpDir);
        mkdir($sessionsDir);
        chmod($sessionsDir, 0o300);

        set_error_handler(static fn () => true);

        try {
            $result = CacheCleanupHelper::cleanTargets($tmpDir, ['sessions']);
            self::assertSame(['sessions' => 0, 'i18n' => 0], $result);
        } finally {
            restore_error_handler();
            chmod($sessionsDir, 0o700);
            rmdir($sessionsDir);
            rmdir($tmpDir);
        }
    }
}
