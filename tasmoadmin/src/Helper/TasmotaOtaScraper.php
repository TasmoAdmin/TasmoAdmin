<?php

namespace TasmoAdmin\Helper;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class TasmotaOtaScraper
{
    private const OTA_URLS = [
        'stable' => 'https://ota.tasmota.com/tasmota/release/',
        'dev' => 'https://ota.tasmota.com/tasmota',
    ];

    private string $url;

    private Client $client;

    public function __construct(string $updateChannel, Client $client)
    {
        $this->url = self::OTA_URLS[$updateChannel];
        $this->client = $client;
    }

    public function getFirmware(): TasmoFirmwareResult
    {
        $crawler = $this->client->request('GET', $this->url);

        $firmwares = $crawler->filter('table tr td:nth-child(2)')->each(function ($node) {
            return new TasmoFirmware(basename($node->text()), $node->text());
        });

        return new TasmoFirmwareResult($this->getVersion($crawler), $firmwares);
    }

    private function getVersion(Crawler  $crawler): string
    {
        $text = $crawler->filter('h2')->innerText();

        preg_match('/((\d+\.)+\d)/', $text, $matches);

        return $matches[1];
    }
}
