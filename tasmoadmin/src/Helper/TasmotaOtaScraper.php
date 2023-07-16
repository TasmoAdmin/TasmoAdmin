<?php

namespace TasmoAdmin\Helper;

use DateTime;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\DomCrawler\Crawler;

class TasmotaOtaScraper
{
    public const ESP8266 = 'ESP8266';
    public const ESP32= 'ESP32';

    private const OTA_URLS = [
        'stable' => [
            self::ESP8266 => 'https://ota.tasmota.com/tasmota/release/',
            self::ESP32 => 'https://ota.tasmota.com/tasmota32/release/',
            ],
        'dev' => [
            self::ESP8266 => 'https://ota.tasmota.com/tasmota/',
            self::ESP32 => 'https://ota.tasmota.com/tasmota32/',
        ],
    ];

    private const RULES = [
      self::ESP8266 => [
          'date_column' => 6,
      ],
        self::ESP32 => [
            'date_column' => 4,
        ],
    ];

    private string $updateChannel;

    private HttpBrowser $client;

    public function __construct(string $updateChannel, HttpBrowser $client)
    {
        $this->updateChannel = $updateChannel;
        $this->client = $client;
    }

    public function getEsp8266Firmware(): TasmotaFirmwareResult
    {
        return $this->getFirmware(self::ESP8266);
    }

    public function getEsp32Firmware(): TasmotaFirmwareResult
    {
        return $this->getFirmware(self::ESP32);
    }

    private function getFirmware(string $type)
    {
        $crawler = $this->client->request('GET', self::OTA_URLS[$this->updateChannel][$type]);

        $firmwares =  $crawler->filter('table tr td:nth-child(2)')->each(function ($node) {
            return new TasmotaFirmware(basename($node->text()), $node->text());
        });

        foreach ($firmwares as $index => $firmware) {
            if (!str_starts_with($firmware->getUrl(), 'http')) {
                unset($firmwares[$index]);
            }
        }

        $version = $this->getVersion($crawler);
        $publishDate =  $this->getPublishDate($crawler, $type);

        return new TasmotaFirmwareResult($version, $publishDate, $firmwares);
    }

    private function getVersion(Crawler  $crawler): string
    {
        $text = $crawler->filter('h2')->innerText();

        preg_match('/((\d+\.)+\d)/', $text, $matches);

        return $matches[1];
    }

    private function getPublishDate(Crawler $crawler, string $type): DateTime
    {
        $dateColumn = self::RULES[$type]['date_column'];

        $dates = $crawler->filter("table tr td:nth-child($dateColumn)")->each(function ($node) {
            return $node->text();
        });

        return DateTime::createFromFormat('Ymd H:i', $dates[0]);
    }
}
