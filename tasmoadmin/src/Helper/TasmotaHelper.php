<?php

namespace TasmoAdmin\Helper;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use TasmoAdmin\Update\AutoFirmwareResult;

class TasmotaHelper
{
    private const CHANGELOG_URLS = [
        'dev' => 'https://raw.githubusercontent.com/arendst/Tasmota/development/CHANGELOG.md',
        'stable' => 'https://raw.githubusercontent.com/arendst/Tasmota/master/CHANGELOG.md',
    ];

    private GithubFlavoredMarkdownConverter $markDownParser;

    private Client $client;

    private TasmotaOtaScraper $tasmotaOtaScraper;

    private string $channel;

    public function __construct(
        GithubFlavoredMarkdownConverter $markDownParser,
        Client $client,
        TasmotaOtaScraper $tasmotaOtaScraper,
        string $channel
    ) {
        $this->markDownParser = $markDownParser;
        $this->client = $client;
        $this->tasmotaOtaScraper = $tasmotaOtaScraper;
        $this->channel = $channel;
    }

    public function getReleaseNotes(): string
    {
        $releaseLog = $this->getContents('https://raw.githubusercontent.com/arendst/Tasmota/development/RELEASENOTES.md');
        $releaseLog = str_replace(['*/', '/*', " *\n"], ['', '', ''], $releaseLog);
        $releaseLog = str_replace(
            'https://github.com/arendst/Tasmota/blob/master/tools/logo/TASMOTA_FullLogo_Vector.svg',
            'https://raw.githubusercontent.com/arendst/Tasmota/master/tools/logo/TASMOTA_FullLogo_Vector.svg',
            $releaseLog
        );
        $releaseLog = $this->markDownParser->convert($releaseLog);
        $releaseLog = $this->replaceIssuesWithUrls($releaseLog);

        return str_replace(
            'https://github.com/arendst/Tasmota/blob/master/tools/logo/TASMOTA_FullLogo_Vector.svg',
            'https://raw.githubusercontent.com/arendst/Tasmota/master/tools/logo/TASMOTA_FullLogo_Vector.svg',
            $releaseLog
        );
    }

    public function getChangelog(): string
    {
        $changeLog = $this->getContents(self::CHANGELOG_URLS[$this->channel]);
        $changeLog = $this->markDownParser->convert($changeLog);

        return $this->replaceIssuesWithUrls($changeLog);
    }

    public function getReleases(): array
    {
        $releases = array_values(array_unique(array_merge(
            $this->getEsp8266Releases(),
            $this->getEsp32Releases()
        )));

        asort($releases);

        return $releases;
    }

    public function getEsp8266Releases(): array
    {
        return $this->getReleaseNames($this->tasmotaOtaScraper->getEsp8266Firmware(), '.bin.gz', '-minimal.bin');
    }

    public function getEsp32Releases(): array
    {
        return $this->getReleaseNames($this->tasmotaOtaScraper->getEsp32Firmware(), '.bin');
    }

    public function getLatestFirmwares(string $configuredFirmware): AutoFirmwareResult
    {
        $isEsp32 = str_starts_with($configuredFirmware, 'tasmota32');

        if ($isEsp32) {
            return $this->getEsp32LatestFirmwares($configuredFirmware);
        }

        return $this->getEsp8266LatestFirmwares($configuredFirmware);
    }

    private function getEsp32LatestFirmwares(string $configuredFirmware): AutoFirmwareResult
    {
        $firmwareResult = $this->tasmotaOtaScraper->getEsp32Firmware();
        foreach ($firmwareResult->getFirmwares() as $asset) {
            if ($asset->getName() === pathinfo($configuredFirmware, PATHINFO_FILENAME).'.bin') {
                $fwUrl = $asset->getUrl();
            }
        }

        if (!isset($fwUrl)) {
            throw new \InvalidArgumentException('Failed to resolve firmware');
        }

        return new AutoFirmwareResult($fwUrl, null, $firmwareResult->getVersion(), $firmwareResult->getPublishDate());
    }

    private function getEsp8266LatestFirmwares(string $configuredFirmware): AutoFirmwareResult
    {
        $firmwareResult = $this->tasmotaOtaScraper->getEsp8266Firmware();
        foreach ($firmwareResult->getFirmwares() as $asset) {
            if ('tasmota-minimal.bin.gz' === $asset->getName()) {
                $fwMinimalUrl = $asset->getUrl();
            }
            if ($asset->getName() === pathinfo($configuredFirmware, PATHINFO_FILENAME).'.bin.gz') {
                $fwUrl = $asset->getUrl();
            }
        }

        if (!isset($fwUrl, $fwMinimalUrl)) {
            throw new \InvalidArgumentException('Failed to resolve firmware');
        }

        return new AutoFirmwareResult($fwUrl, $fwMinimalUrl, $firmwareResult->getVersion(), $firmwareResult->getPublishDate());
    }

    private function getContents(string $url): string
    {
        try {
            $url = "{$url}?r=".time();

            return $this->client->get($url)->getBody()->getContents();
        } catch (GuzzleException $exception) {
            return sprintf('Failed to load %s - %s', $url, $exception->getMessage());
        }
    }

    private function replaceIssuesWithUrls(string $content): string
    {
        $tasmotaIssueUrl = 'https://github.com/arendst/Tasmota/issues/';

        return preg_replace(
            '/\B#([\d]+)/',
            "<a href='{$tasmotaIssueUrl}$1' target='_blank'>#$1</a>",
            $content
        );
    }

    private function getReleaseNames(TasmotaFirmwareResult $firmwareResult, string $suffix, ?string $excludedPart = null): array
    {
        $releases = [];
        foreach ($firmwareResult->getFirmwares() as $asset) {
            if (null !== $excludedPart && str_contains($asset->getName(), $excludedPart)) {
                continue;
            }
            if (!str_ends_with($asset->getName(), $suffix)) {
                continue;
            }

            $releases[] = substr($asset->getName(), 0, -strlen($suffix));
        }

        $releases = array_values(array_unique($releases));
        asort($releases);

        return $releases;
    }
}
