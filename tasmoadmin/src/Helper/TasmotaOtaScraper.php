<?php

namespace TasmoAdmin\Helper;

use Goutte\Client;
use RuntimeException;
use Symfony\Component\DomCrawler\Crawler;

class TasmotaOtaScraper
{
    private string $url;

    private Client $client;

    public function __construct(string $url, Client $client)
    {
        $this->url = $url;
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
