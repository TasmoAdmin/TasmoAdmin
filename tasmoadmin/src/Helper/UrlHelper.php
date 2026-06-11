<?php

namespace TasmoAdmin\Helper;

use TasmoAdmin\Config;

class UrlHelper
{
    private string $resourceUrl;

    private string $resourceDir;

    private bool $minimizeResources;

    private ?string $currentGitTag;

    public function __construct(Config $config, string $resourceUrl, string $resourceDir)
    {
        $this->resourceUrl = $resourceUrl;
        $this->resourceDir = $resourceDir;
        $this->minimizeResources = '1' === $config->read('minimize_resources');
        $this->currentGitTag = $config->read('current_git_tag');
    }

    public function style(string $filename): string
    {
        $csspath = $this->resourceUrl.'css/';
        $cssReal = $this->resourceDir.'css/';
        $min = '';
        if ($this->minimizeResources) {
            $min = '.min';
        }

        $path = $filename.$min.'.css';
        if (file_exists($cssReal.$path)) {
            $filepath = $csspath.$path.$this->getCacheTag($cssReal.$path);
        } else {
            $filepath = $csspath.$filename.'.css'.$this->getCacheTag($cssReal.$filename.'.css');
        }

        return $filepath;
    }

    public function js(string $filename): string
    {
        $jspath = $this->resourceUrl.'js/';
        $jsReal = $this->resourceDir.'js/';
        $min = '';
        if ($this->minimizeResources) {
            $min = '.min';
        }

        $path = $filename.$min.'.js';
        if (file_exists($jsReal.$path)) {
            $filepath = $jspath.$path.$this->getCacheTag($jsReal.$path);
        } else {
            $filepath = $jspath.$filename.'.js'.$this->getCacheTag($jsReal.$filename.'.js');
        }

        return $filepath;
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
