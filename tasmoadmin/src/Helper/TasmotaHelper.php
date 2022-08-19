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

    private TasmotaOtaScraper $tasmotaOtaScraper;

    public function __construct(Parsedown $markDownParser, Client $client, TasmotaOtaScraper $tasmotaOtaScraper)
    {
        $this->markDownParser = $markDownParser;
        $this->client = $client;
        $this->tasmotaOtaScraper = $tasmotaOtaScraper;
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
        $firmwareResult = $this->getLatestRelease();
        $tasmotaReleases = [];
        foreach ($firmwareResult->getFirmares() as $asset) {
            if (strpos($asset->getName(), "-minimal.bin") !== false) {
                continue;
            }

            $tasmotaReleases[] = substr($asset->getName(), 0, strpos($asset->getName(), "."));
        }


        $tasmotaReleases = array_unique($tasmotaReleases);
        asort($tasmotaReleases);

        return $tasmotaReleases;
    }

    public function getLatestFirmwares(string $ext, string $configuredFirmware): AutoFirmwareResult
    {
        $firmwareResult = $this->getLatestRelease();

        foreach ($firmwareResult->getFirmares() as $asset) {
            if ($asset->getName() === "tasmota-minimal" . "." . $ext) {
                $fwMinimalUrl = $asset->getUrl();
            }
            if ($asset->getName() === pathinfo($configuredFirmware, PATHINFO_FILENAME) . "." . $ext) {
                $fwUrl = $asset->getUrl();
            }
        }

        if (!isset($fwUrl, $fwMinimalUrl)) {
            throw new InvalidArgumentException('Failed to resolve firmware');
        }

        return new AutoFirmwareResult($fwMinimalUrl, $fwUrl, $firmwareResult->getVersion(), $firmwareResult->getPublishDate());
    }

    private function getLatestRelease(): TasmoFirmwareResult
    {
        return $this->tasmotaOtaScraper->getFirmware();
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
