<?php

namespace TasmoAdmin\Helper;

use TasmoAdmin\Config;

class UrlHelper
{
    private string $resourceUrl;

    private string $resourceDir;

    private ?string $currentGitTag;

    private array $manifest = [];

    public function __construct(Config $config, string $resourceUrl, string $resourceDir)
    {
        $this->resourceUrl = $resourceUrl;
        $this->resourceDir = $resourceDir;
        $this->currentGitTag = $config->read('current_git_tag');

        $manifestPath = $resourceDir . 'manifest.json';
        if (file_exists($manifestPath)) {
            $this->manifest = json_decode(file_get_contents($manifestPath), true);
        }
    }

    public function style(string $filename): string
    {
        $csspath = $this->resourceUrl.'css/';
        $cssReal = $this->resourceDir.'css/';

        $name = basename($filename);
        if (isset($this->manifest[$name])) {
            return $csspath . 'compiled/' . $this->manifest[$name];
        }

        return $csspath.$filename.'.css'.$this->getCacheTag($cssReal.$filename.'.css');
    }

    public function js(string $filename): string
    {
        $jspath = $this->resourceUrl.'js/';
        $jsReal = $this->resourceDir.'js/';

        $name = basename($filename);
        if (isset($this->manifest[$name])) {
            return $jspath . 'compiled/' . $this->manifest[$name];
        }

        return $jspath.$filename.'.js'.$this->getCacheTag($jsReal.$filename.'.js');
    }

    private function getCacheTag(?string $assetPath = null): string
    {
        if (null !== $assetPath && file_exists($assetPath)) {
            return '?_='.filemtime($assetPath);
        }

        $cacheTag = $this->currentGitTag;
        if (empty($cacheTag)) {
            $cacheTag = time();
        }
        $cacheTag = str_replace('.', '', $cacheTag);

        return '?_='.$cacheTag;
    }
}
