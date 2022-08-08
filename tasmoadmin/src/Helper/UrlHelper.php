<?php

namespace TasmoAdmin\Helper;

use TasmoAdmin\Config;

class UrlHelper
{
    private Config $config;

    private string $baseUrl;

    private string $resourceUrl;

    public function __construct(Config $config,  string $baseUrl, string $resourceUrl)
    {
        $this->config = $config;
        $this->baseUrl = $baseUrl;
        $this->resourceUrl = $resourceUrl;
    }

    public function style(string $filename, ?string $csspath = null): string
    {
        if ($csspath === null) {
            $csspath = $this->resourceUrl . "css/";
        } else {
            $csspath = $this->baseUrl . $csspath;
        }

        $cacheTag = time();
        $min = "";
        if ($this->config->read("minimize_resources") === "1") {
            $cacheTag = $this->config->read("current_git_tag");
            if (empty($cacheTag)) {
                $cacheTag = time();
            }
            $min = ".min";
        }
        $cacheTag = str_replace(".", "", $cacheTag);
        $cacheTag = "?_=" . $cacheTag;

        $path = $filename . $min . ".css";
        if (file_exists($csspath . $path)) {
            $filepath = $csspath . $path . $cacheTag;
        } else {
            $filepath = $csspath . $filename . ".css" . $cacheTag;
        }

        return $filepath;
    }

    public function js(string $filename, ?string $jspath = null): string
    {
        if ($jspath === null) {
            $jspath = $this->resourceUrl . "js/";
        } else {
            $jspath = $this->baseUrl . $jspath;
        }

        $cacheTag = time();
        $min = "";
        if ($this->config->read("minimize_resources") === "1") {
            $cacheTag = $this->config->read("current_git_tag");
            if (empty($cacheTag)) {
                $cacheTag = time();
            }
            $min = ".min";
        }
        $cacheTag = str_replace(".", "", $cacheTag);
        $cacheTag = "?_=" . $cacheTag;

        $path = $filename . $min . ".js";
        if (file_exists($jspath . $path)) {
            $filepath = $jspath . $path . $cacheTag;
        } else {
            $filepath = $jspath . $filename . ".js" . $cacheTag;
        }

        return $filepath;
    }
}
