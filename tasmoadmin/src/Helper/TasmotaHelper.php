<?php

namespace TasmoAdmin\Helper;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use InvalidArgumentException;
use Parsedown;
use stdClass;
use TasmoAdmin\Update\AutoFirmwareResult;

class TasmotaHelper
{
    private Parsedown $markDownParser;

    private Client $client;

    public function __construct(Parsedown $markDownParser, Client $client)
    {
        $this->markDownParser = $markDownParser;
        $this->client = $client;
    }

    public function getReleaseNotes(): string
    {
        $releaseLog = $this->getContents('https://raw.githubusercontent.com/arendst/Tasmota/development/RELEASENOTES.md');
        $releaseLog = str_replace(["*/", "/*", " *\n"], ["", "", ""], $releaseLog);
        $releaseLog = str_replace("https://github.com/arendst/Tasmota/blob/master/tools/logo/TASMOTA_FullLogo_Vector.svg",
            "https://raw.githubusercontent.com/arendst/Tasmota/master/tools/logo/TASMOTA_FullLogo_Vector.svg",
            $releaseLog);
        $releaseLog = $this->markDownParser->parse($releaseLog);
        $releaseLog = $this->replaceIssuesWithUrls($releaseLog);
        $releaseLog = str_replace("https://github.com/arendst/Tasmota/blob/master/tools/logo/TASMOTA_FullLogo_Vector.svg",
            "https://raw.githubusercontent.com/arendst/Tasmota/master/tools/logo/TASMOTA_FullLogo_Vector.svg",
            $releaseLog);

        return $releaseLog;
    }

    public function getChangelog(): string
    {
        $changeLog = $this->getContents('https://raw.githubusercontent.com/arendst/Tasmota/master/CHANGELOG.md');
        $changeLog = $this->markDownParser->parse($changeLog);
        $changeLog = $this->replaceIssuesWithUrls($changeLog);

        return $changeLog;
    }

    public function getReleases(): array
    {
        $release = $this->getLatestRelease();
        $tasmotaReleases = [];
        if (!empty($release->assets)) {
            foreach ($release->assets as $asset) {
                if (strpos($asset->name, ".bin.gz") !== false || strpos($asset->name, "-minimal.bin") !== false) {
                    continue;
                }

                $tasmotaReleases[] = substr($asset->name, 0, strpos($asset->name, "."));
            }
        } else {
            $tasmotaReleases = [
                "tasmota-BG.bin", "tasmota-BR.bin", "tasmota-CN.bin", "tasmota-CZ.bin", "tasmota-DE.bin",
                "tasmota-display.bin", "tasmota-ES.bin", "tasmota-FR.bin", "tasmota-GR.bin", "tasmota-HE.bin",
                "tasmota-HU.bin", "tasmota-ir.bin", "tasmota-ircustom.bin", "tasmota-IT.bin", "tasmota-knx.bin",
                "tasmota-KO.bin", "tasmota-lite.bin", "tasmota-NL.bin", "tasmota-PL.bin", "tasmota-PT.bin",
                "tasmota-RO.bin", "tasmota-RU.bin", "tasmota-SE.bin", "tasmota-sensors.bin", "tasmota-SK.bin",
                "tasmota-TR.bin", "tasmota-TW.bin", "tasmota-UK.bin", "tasmota-zbbridge.bin", "tasmota.bin",
                "tasmota32-BG.bin", "tasmota32-BR.bin", "tasmota32-CN.bin", "tasmota32-CZ.bin", "tasmota32-DE.bin",
                "tasmota32-display.bin", "tasmota32-ES.bin", "tasmota32-FR.bin", "tasmota32-GR.bin", "tasmota32-HE.bin",
                "tasmota32-ir.bin", "tasmota32-ircustom.bin", "tasmota32-knx.bin", "tasmota32-lite.bin",
                "tasmota32-PL.bin", "tasmota32-PT.bin", "tasmota32-RO.bin", "tasmota32-RU.bin", "tasmota32-SE.bin",
                "tasmota32-sensors.bin", "tasmota32-SK.bin", "tasmota32-TR.bin", "tasmota32-TW.bin", "tasmota32-UK.bin",
                "tasmota32-webcam.bin", "tasmota32.bin",
            ];
        }

        $tasmotaReleases = array_unique($tasmotaReleases);
        asort($tasmotaReleases);

        return $tasmotaReleases;
    }

    public function getLatestFirmwares(string $ext, string $configuredFirmware): AutoFirmwareResult
    {
        $release = $this->getLatestRelease();

        foreach ($release->assets as $binfileData) {
            if ($binfileData->name === "tasmota-minimal" . "." . $ext) {
                $fwMinimalUrl = $binfileData->browser_download_url;
            }
            if ($binfileData->name === pathinfo($configuredFirmware, PATHINFO_FILENAME) . "." . $ext) {
                $fwUrl = $binfileData->browser_download_url;
            }
        }

        if (!isset($fwUrl, $fwMinimalUrl)) {
            throw new InvalidArgumentException('Failed to resolve firmware');
        }

        return new AutoFirmwareResult($fwMinimalUrl, $fwUrl, $release->tag_name, $release->published_at);
    }

    private function getLatestRelease(): stdClass
    {
        $tasmotaRepoReleaseUrl = "https://api.github.com/repos/arendst/Tasmota/releases/latest";
        try {
            return json_decode($this->client->get($tasmotaRepoReleaseUrl)->getBody()->getContents());
        } catch (ClientException $e) {
            return new stdClass();
        }
    }

    private function getContents(string $url): string
    {
        $url = "${url}?r=" . time();
        return $this->client->get($url)->getBody()->getContents();
    }

    private function replaceIssuesWithUrls(string $content): string
    {
        $tasmotaIssueUrl = "https://github.com/arendst/Tasmota/issues/";
        return preg_replace(
            "/\B#([\d]+)/",
            "<a href='$tasmotaIssueUrl$1' target='_blank'>#$1</a>",
            $content
        );
    }
}
