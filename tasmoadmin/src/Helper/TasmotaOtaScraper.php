<?php

namespace TasmoAdmin\Helper;

use DateTime;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\DomCrawler\Crawler;

class TasmotaOtaScraper
{
    private const OTA_URLS = [
        'stable' => 'https://ota.tasmota.com/tasmota/release/',
        'dev' => 'https://ota.tasmota.com/tasmota',
    ];

    private string $url;

    private HttpBrowser $client;

    public function __construct(string $updateChannel, HttpBrowser $client)
    {
        $this->url = self::OTA_URLS[$updateChannel];
        $this->client = $client;
    }

    public function getFirmware(): TasmotaFirmwareResult
    {
        $crawler = $this->client->request('GET', $this->url);

        $firmwares =  $crawler->filter('table tr td:nth-child(2)')->each(function ($node) {
            return new TasmotaFirmware(basename($node->text()), $node->text());
        });

        $version = $this->getVersion($crawler);
        $publishDate =  $this->getPublishDate($crawler);

        return new TasmotaFirmwareResult($version, $publishDate, $firmwares);
    }

    private function getVersion(Crawler  $crawler): string
    {
        $text = $crawler->filter('h2')->innerText();

        preg_match('/((\d+\.)+\d)/', $text, $matches);

        return $matches[1];
    }

    private function getPublishDate(Crawler $crawler): DateTime
    {
        $dates = $crawler->filter('table tr td:nth-child(6)')->each(function ($node) {
            return $node->text();
        });

        return DateTime::createFromFormat('Ymd H:i', $dates[0]);
    }
}
