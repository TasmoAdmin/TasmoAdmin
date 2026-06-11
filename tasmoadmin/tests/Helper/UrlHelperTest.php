<?php

namespace Tests\TasmoAdmin\Helper;

use PHPUnit\Framework\TestCase;
use TasmoAdmin\Config;
use TasmoAdmin\Helper\UrlHelper;

class UrlHelperTest extends TestCase
{
    private string $rootDir;

    private string $dataDir;

    private string $resourceDir;

    protected function setUp(): void
    {
        $this->rootDir = sys_get_temp_dir().'/tasmoadmin-url-helper-'.bin2hex(random_bytes(6));
        $this->dataDir = $this->rootDir.'/data/';
        $this->resourceDir = $this->rootDir.'/resources/';

        mkdir($this->dataDir, 0o755, true);
        mkdir($this->resourceDir.'css', 0o755, true);
        mkdir($this->resourceDir.'js', 0o755, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->rootDir);
    }

    public function testStyleUsesMinifiedAssetWhenEnabled(): void
    {
        $config = $this->getConfig([
            'minimize_resources' => '1',
            'current_git_tag' => 'v5.1.0',
        ]);
        $assetPath = $this->resourceDir.'css/app.min.css';
        file_put_contents($assetPath, 'body{}');

        $helper = new UrlHelper($config, '/assets/', $this->resourceDir);

        self::assertSame(
            '/assets/css/app.min.css?_='.filemtime($assetPath),
            $helper->style('app')
        );
    }

    public function testStyleFallsBackToNonMinifiedAssetAndGitTagCache(): void
    {
        $config = $this->getConfig([
            'minimize_resources' => '1',
            'current_git_tag' => 'v5.1.0',
        ]);

        $helper = new UrlHelper($config, '/assets/', $this->resourceDir);

        self::assertSame('/assets/css/app.css?_=v510', $helper->style('app'));
    }

    public function testJsUsesNonMinifiedAssetAndTimestampCache(): void
    {
        $config = $this->getConfig([
            'minimize_resources' => '0',
            'current_git_tag' => 'v5.1.0',
        ]);
        $assetPath = $this->resourceDir.'js/app.js';
        file_put_contents($assetPath, 'console.log("ok");');

        $helper = new UrlHelper($config, '/assets/', $this->resourceDir);

        self::assertSame(
            '/assets/js/app.js?_='.filemtime($assetPath),
            $helper->js('app')
        );
    }

    public function testJsFallsBackToCurrentTimeWhenNoAssetOrGitTagExists(): void
    {
        $config = $this->getConfig([
            'minimize_resources' => '0',
            'current_git_tag' => '',
        ]);
        $helper = new UrlHelper($config, '/assets/', $this->resourceDir);

        $result = $helper->js('missing');

        self::assertMatchesRegularExpression('#^/assets/js/missing\.js\?_=\d+$#', $result);
    }

    private function getConfig(array $overrides): Config
    {
        $config = new Config($this->dataDir, $this->rootDir.'/');
        $config->writeAll($overrides);

        return $config;
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = array_diff(scandir($directory), ['.', '..']);
        foreach ($items as $item) {
            $path = $directory.'/'.$item;
            if (is_dir($path) && !is_link($path)) {
                $this->removeDirectory($path);

                continue;
            }

            unlink($path);
        }

        rmdir($directory);
    }
}
