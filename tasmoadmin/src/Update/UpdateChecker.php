<?php

namespace TasmoAdmin\Update;

use GuzzleHttp\Client;

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
            "update" => FALSE,
            "error"  => FALSE,
            "msg"    => "",
        ];

        if (!$action) {
            return $result;
        }

        $release = $this->doRequest($action);
        if (is_array($release) && isset($release["ERROR"])) {
            $result["error"] = TRUE;
            $result["msg"]   = $release["ERROR"];
        }
        else {
            if (is_array($release)) {
                $release = $release[0];
            }
            if (isset($release->tag_name)) {
                $result["latest_tag"] = $release->tag_name;

                if ($this->currentTag !== $result["latest_tag"]) {
                    $result["update"] = TRUE;
                }
                if ($this->updateChannel === "dev") {
                    $result["update"] = TRUE;
                }
            }
        }

        if (empty($release->assets[1])) {
            $result["error"]  = TRUE;
            $result["msg"]    = __("DOWNLOAD_MISSING", "SELFUPDATE");
            $result["update"] = FALSE;
        }
        else {
            $result["release_url"] = $release->assets[1]->browser_download_url;
        }

        return $result;
    }

    private function doRequest($action = "") {
        ini_set("max_execution_time", "240");
        set_time_limit("240");

        $url = $this->repoUrl . $action;
        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
        curl_setopt(
            $ch,
            CURLOPT_USERAGENT,
            'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13'
        );
        $result = json_decode(curl_exec($ch));
        if (curl_error($ch)) {
            $result = [
                "ERROR" => __("ERROR_CURL", "SELFUPDATE") . " - " . curl_errno($ch) . ": " . curl_error(
                        $ch
                    ),
            ];
        }
        curl_close($ch);

        ini_set("max_execution_time", 30);

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
