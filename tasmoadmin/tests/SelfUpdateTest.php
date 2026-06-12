<?php

namespace Tests\TasmoAdmin;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use TasmoAdmin\Config;
use TasmoAdmin\SelfUpdate;

if (!defined('_DATADIR_')) {
    define('_DATADIR_', sys_get_temp_dir().'/tasmoadmin-selfupdate-tests/data/');
}
if (!defined('_TMPDIR_')) {
    define('_TMPDIR_', sys_get_temp_dir().'/tasmoadmin-selfupdate-tests/tmp/');
}
if (!defined('_APPROOT_')) {
    define('_APPROOT_', sys_get_temp_dir().'/tasmoadmin-selfupdate-tests/app/');
}

class SelfUpdateTest extends TestCase
{
    private string $baseDir;

    protected function setUp(): void
    {
        $this->baseDir = dirname(rtrim(_DATADIR_, '/'));
        $this->resetDirectoryTree();

        mkdir(_DATADIR_, 0o755, true);
        mkdir(_DATADIR_.'updates', 0o755, true);
        mkdir(_TMPDIR_, 0o755, true);
        mkdir(_APPROOT_, 0o755, true);
    }

    protected function tearDown(): void
    {
        $this->resetDirectoryTree();
    }

    public function testUpdateReturnsFailureWhenDownloadedZipIsEmpty(): void
    {
        $config = $this->getConfig([
            'current_git_tag' => 'v5.0.0',
            'update_channel' => 'stable',
        ]);
        $updater = new SelfUpdate($config, $this->getDownloadClient(static function (string $sink): void {
            file_put_contents($sink, '');
        }));

        $result = $updater->update('https://example.com/release.zip', 'v5.1.0');

        self::assertFalse($result['success']);
        self::assertContains('SELFUPDATE_ERROR_COULD_NOT_DOWNLOAD_ZIP: ', $result['logs']);
        self::assertSame('v5.0.0', $config->read('current_git_tag'));
    }

    public function testUpdateCopiesStableReleaseAndUpdatesVersion(): void
    {
        $config = $this->getConfig([
            'current_git_tag' => 'v5.0.0',
            'update_channel' => 'stable',
        ]);
        $zipPath = $this->createReleaseZip([
            'tasmoadmin/' => null,
            'tasmoadmin/marker.txt' => 'fresh release',
        ]);

        $updater = new SelfUpdate($config, $this->getDownloadClient(static function (string $sink) use ($zipPath): void {
            copy($zipPath, $sink);
        }));

        $result = $updater->update('https://example.com/release.zip', 'v5.1.0');

        self::assertTrue($result['success']);
        self::assertSame('v5.1.0', $config->read('current_git_tag'));
        self::assertFileExists(_APPROOT_.'marker.txt');
        self::assertSame('fresh release', file_get_contents(_APPROOT_.'marker.txt'));
        self::assertContains('SELFUPDATE_CONTENT_COPY_DONE: ', $result['logs']);
        self::assertContains('SELFUPDATE_TEMP_DIR_DELETED: ', $result['logs']);
        self::assertContains('SELFUPDATE_OLD_TAG_VERSION: v5.0.0', $result['logs']);
        self::assertContains('SELFUPDATE_NEW_TAG_VERSION: v5.1.0', $result['logs']);
        self::assertDirectoryDoesNotExist(_TMPDIR_.'tasmoadmin');
    }

    public function testUpdateSkipsCopyOnDevChannelButStillUpdatesVersion(): void
    {
        $config = $this->getConfig([
            'current_git_tag' => 'v5.0.0',
            'update_channel' => 'dev',
        ]);
        $zipPath = $this->createReleaseZip([
            'tasmoadmin/' => null,
            'tasmoadmin/marker.txt' => 'dev release',
        ]);

        $updater = new SelfUpdate($config, $this->getDownloadClient(static function (string $sink) use ($zipPath): void {
            copy($zipPath, $sink);
        }));

        $result = $updater->update('https://example.com/release.zip', 'v5.1.0-dev');

        self::assertTrue($result['success']);
        self::assertSame('v5.1.0-dev', $config->read('current_git_tag'));
        self::assertFileDoesNotExist(_APPROOT_.'marker.txt');
        self::assertContains('SELFUPDATE_CONTENT_COPY_SKIP_DEV: ', $result['logs']);
        self::assertDirectoryDoesNotExist(_TMPDIR_.'tasmoadmin');
    }

    public function testUpdateStopsWhenPreInstallChecksFail(): void
    {
        $config = $this->getConfig([
            'current_git_tag' => 'v5.0.0',
            'update_channel' => 'stable',
        ]);
        $zipPath = $this->createReleaseZip([
            'tasmoadmin/' => null,
            'tasmoadmin/marker.txt' => 'should not copy',
            'tasmoadmin/includes/' => null,
            'tasmoadmin/includes/preinstallchecks.php' => <<<'PHP'
                <?php
                return new class {
                    public function run(): object
                    {
                        return new class {
                            public function isValid(): bool
                            {
                                return false;
                            }

                            public function getErrors(): array
                            {
                                return ['first failure', 'second failure'];
                            }
                        };
                    }
                };
                PHP,
        ]);

        $updater = new SelfUpdate($config, $this->getDownloadClient(static function (string $sink) use ($zipPath): void {
            copy($zipPath, $sink);
        }));

        $result = $updater->update('https://example.com/release.zip', 'v5.1.0');

        self::assertFalse($result['success']);
        self::assertContains('first failure', $result['logs']);
        self::assertContains('second failure', $result['logs']);
        self::assertFileDoesNotExist(_APPROOT_.'marker.txt');
        self::assertSame('v5.0.0', $config->read('current_git_tag'));
    }

    public function testUpdateReturnsFailureWhenDownloadedFileIsNotAZipArchive(): void
    {
        $config = $this->getConfig([
            'current_git_tag' => 'v5.0.0',
            'update_channel' => 'stable',
        ]);
        $updater = new SelfUpdate($config, $this->getDownloadClient(static function (string $sink): void {
            file_put_contents($sink, 'not-a-zip');
        }));

        $result = $updater->update('https://example.com/release.zip', 'v5.1.0');

        self::assertFalse($result['success']);
        self::assertStringContainsString('SELFUPDATE_ERROR_FILE_EXTRACTED_TO:', implode("\n", $result['logs']));
        self::assertStringContainsString(_DATADIR_.'updates/tasmoadmin.zip', implode("\n", $result['logs']));
        self::assertSame('v5.0.0', $config->read('current_git_tag'));
    }

    public function testUpdateContinuesWhenPreInstallChecksThrow(): void
    {
        $config = $this->getConfig([
            'current_git_tag' => 'v5.0.0',
            'update_channel' => 'stable',
        ]);
        $zipPath = $this->createReleaseZip([
            'tasmoadmin/' => null,
            'tasmoadmin/marker.txt' => 'fresh release',
            'tasmoadmin/includes/' => null,
            'tasmoadmin/includes/preinstallchecks.php' => <<<'PHP'
                <?php
                throw new RuntimeException('preinstall boom');
                PHP,
        ]);

        $updater = new SelfUpdate($config, $this->getDownloadClient(static function (string $sink) use ($zipPath): void {
            copy($zipPath, $sink);
        }));

        $result = $updater->update('https://example.com/release.zip', 'v5.1.0');

        self::assertTrue($result['success']);
        self::assertContains('Failed to perform pre-install checks', $result['logs']);
        self::assertContains('preinstall boom', $result['logs']);
        self::assertSame('v5.1.0', $config->read('current_git_tag'));
        self::assertFileExists(_APPROOT_.'marker.txt');
    }

    public function testUpdateFailsWhenArchiveDoesNotContainTopLevelDirectory(): void
    {
        $config = $this->getConfig([
            'current_git_tag' => 'v5.0.0',
            'update_channel' => 'stable',
        ]);
        $zipPath = $this->createReleaseZip([
            'marker.txt' => 'bad archive layout',
        ]);

        $updater = $this->getUpdaterWithZip($config, $zipPath);

        $result = $updater->update('https://example.com/release.zip', 'v5.1.0');

        self::assertFalse($result['success']);
        self::assertContains('SELFUPDATE_ERROR_EMPTY_FIRST_DIR: ', $result['logs']);
        self::assertSame('v5.0.0', $config->read('current_git_tag'));
        self::assertFileDoesNotExist(_APPROOT_.'marker.txt');
    }

    public function testUpdateFailsWhenReleaseCannotBeCopiedIntoAppRoot(): void
    {
        $config = $this->getConfig([
            'current_git_tag' => 'v5.0.0',
            'update_channel' => 'stable',
        ]);
        $zipPath = $this->createReleaseZip([
            'tasmoadmin/' => null,
            'tasmoadmin/marker.txt' => 'fresh release',
        ]);

        $appRootFile = rtrim(_APPROOT_, '/');
        rmdir($appRootFile);
        file_put_contents($appRootFile, 'not-a-directory');

        $updater = $this->getUpdaterWithZip($config, $zipPath);
        $this->expectOutputString('');

        $result = $updater->update('https://example.com/release.zip', 'v5.1.0');

        self::assertFalse($result['success']);
        self::assertContains('SELFUPDATE_ERROR_COULD_NOT_COPY_UPDATE: ', $result['logs']);
        self::assertSame('v5.0.0', $config->read('current_git_tag'));
    }

    private function getConfig(array $overrides): Config
    {
        $config = new Config(_DATADIR_, _APPROOT_);
        $config->writeAll($overrides);

        return $config;
    }

    private function getDownloadClient(callable $writer): Client
    {
        $client = $this->createMock(Client::class);
        $client->expects(self::once())
            ->method('get')
            ->with(
                self::callback(static fn (mixed $url): bool => is_string($url)),
                self::callback(static function (array $options) use ($writer): bool {
                    $writer($options['sink']);

                    return true;
                })
            )
            ->willReturn(new Response(200))
        ;

        return $client;
    }

    private function getUpdaterWithZip(Config $config, string $zipPath): SelfUpdate
    {
        return new SelfUpdate($config, $this->getDownloadClient(static function (string $sink) use ($zipPath): void {
            copy($zipPath, $sink);
        }));
    }

    /**
     * @param array<string, null|string> $entries
     */
    private function createReleaseZip(array $entries): string
    {
        $zipPath = $this->baseDir.'/release-'.bin2hex(random_bytes(4)).'.zip';
        $zip = new \ZipArchive();
        $opened = $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        self::assertSame(true, $opened);

        foreach ($entries as $path => $content) {
            if (str_ends_with($path, '/')) {
                $zip->addEmptyDir(rtrim($path, '/'));

                continue;
            }

            $zip->addFromString($path, $content ?? '');
        }

        $zip->close();

        return $zipPath;
    }

    private function resetDirectoryTree(): void
    {
        if (!is_dir($this->baseDir)) {
            mkdir($this->baseDir, 0o755, true);

            return;
        }

        $this->removeDirectoryContents($this->baseDir);
    }

    private function removeDirectoryContents(string $directory): void
    {
        $items = array_diff(scandir($directory), ['.', '..']);
        foreach ($items as $item) {
            $path = $directory.'/'.$item;
            if (is_dir($path) && !is_link($path)) {
                $this->removeDirectoryContents($path);
                rmdir($path);

                continue;
            }

            unlink($path);
        }
    }
}
