<?php

namespace TasmoAdmin\Helper;

use TasmoAdmin\Config;

class UrlHelper
{
    private string $baseUrl;

    private string $resourceUrl;

    private string $resourceDir;

    private bool $minimizeResources;

    private ?string $currentGitTag;

    public function __construct(Config $config, string $baseUrl, string $resourceUrl, string $resourceDir)
    {
        $this->baseUrl = $baseUrl;
        $this->resourceUrl = $resourceUrl;
        $this->resourceDir = $resourceDir;
        $this->minimizeResources = $config->read("minimize_resources") === "1";
        $this->currentGitTag = $config->read("current_git_tag");
    }

    private function getCacheTag(): string
    {
        $cacheTag = $this->currentGitTag;
        if (empty($cacheTag)) {
            $cacheTag = time();
        }

        $cacheTag = str_replace(".", "", $cacheTag);

        return "?_=" . $cacheTag;
    }

    public function style(string $filename): string
    {
        $csspath = $this->resourceUrl . "css/";
        $cssReal = $this->resourceDir . "css/";
        $cacheTag = $this->getCacheTag();
        $min = "";
        if ($this->minimizeResources) {
            $min = ".min";
        }

        $path = $filename . $min . ".css";
        if (file_exists($cssReal . $path)) {
            $filepath = $csspath . $path . $cacheTag;
        } else {
            $filepath = $csspath . $filename . ".css" . $cacheTag;
        }

        return $filepath;
    }

    public function js(string $filename): string
    {

        $jspath = $this->resourceUrl . "js/";
        $jsReal = $this->resourceDir . "js/";
        $cacheTag = $this->getCacheTag();
        $min = "";
        if ($this->minimizeResources) {
            $min = ".min";
        }

        $path = $filename . $min . ".js";
        if (file_exists($jsReal . $path)) {
            $filepath = $jspath . $path . $cacheTag;
        } else {
            $filepath = $jspath . $filename . ".js" . $cacheTag;
        }

        return $filepath;
    }
}
