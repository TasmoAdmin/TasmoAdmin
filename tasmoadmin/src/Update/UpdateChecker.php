<?php

namespace TasmoAdmin\Update;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class UpdateChecker
{
    private string $repoUrl = "https://api.github.com/repos/TasmoAdmin/TasmoAdmin";

    private string $updateChannel;

    private string $currentTag;

    private Client $client;

    public function __construct(string $updateChannel, string $currentTag, Client $client)
    {
        $this->updateChannel = $updateChannel;
        $this->currentTag = $currentTag;
        $this->client = $client;
    }

    public function checkForUpdate(): array
    {
        $action = $this->action();

        $result = [
            "update" => false,
            "error"  => false,
            "msg"    => "",
        ];

        if (!$action) {
            return $result;
        }

        $release = $this->doRequest($action);
        if (is_array($release) && isset($release["ERROR"])) {
            $result["error"] = true;
            $result["msg"]   = $release["ERROR"];

            return $result;
        }

        if (is_array($release)) {
            $release = $release[0];
        }

        if (isset($release->tag_name)) {
            $result["latest_tag"] = $release->tag_name;

            if ($this->currentTag !== $result["latest_tag"]) {
                $result["update"] = true;
            }
            if ($this->updateChannel === "dev") {
                $result["update"] = true;
            }
        }

        if (empty($release->assets[1])) {
            $result["error"]  = true;
            $result["msg"]    = __("DOWNLOAD_MISSING", "SELFUPDATE");
            $result["update"] = false;

            return $result;
        }

        $result["release_url"] = $release->assets[1]->browser_download_url;
        return $result;
    }

    private function doRequest(string $action)
    {
        $url = $this->repoUrl . $action;
        try {
            $result = json_decode($this->client->get($url)->getBody()->getContents());
        } catch (RequestException $e) {
            $result = [
                "ERROR" => __("ERROR_CURL", "SELFUPDATE") . " - " . $e->getMessage()
            ];
        }
            return $result;
    }

    private function action(): ?string
    {
        $action  = null;

        if ($this->updateChannel === "stable") {
            $action = "/releases/latest";
        }
        elseif (in_array($this->updateChannel, ["beta", "dev"])) {
            $action = "/releases";
        }

        return $action;
    }
}
