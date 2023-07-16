<?php

namespace Tests\TasmoAdmin\Helper;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use TasmoAdmin\Helper\TasmotaOtaScraper;
use PHPUnit\Framework\TestCase;
use Tests\TasmoAdmin\TestUtils;

class TasmotaOtaScraperTest extends TestCase
{
    public function testGetStableEsp8266Firmware(): void
    {
        $scraper = new TasmotaOtaScraper('stable', new HttpBrowser($this->getHttpClient()));
        $result = $scraper->getEsp8266Firmware();
        self::assertEquals('12.1.1', $result->getVersion());
        self::assertEquals('2022-08-25', $result->getPublishDate()->format('Y-m-d'));
    }

    public function testGetDevEsp8266Firmware(): void
    {
        $scraper = new TasmotaOtaScraper('dev', new HttpBrowser($this->getHttpClient()));
        $result = $scraper->getEsp8266Firmware();
        self::assertEquals('12.1.1.1', $result->getVersion());
        self::assertEquals('2022-08-30', $result->getPublishDate()->format('Y-m-d'));
    }

    public function testGetStableEsp32Firmware(): void
    {
        $scraper = new TasmotaOtaScraper('stable', new HttpBrowser($this->getHttpClient()));
        $result = $scraper->getEsp32Firmware();
        self::assertEquals('13.0.0', $result->getVersion());
        self::assertEquals('2023-06-26', $result->getPublishDate()->format('Y-m-d'));
    }

    public function testGetDevEsp32Firmware(): void
    {
        $scraper = new TasmotaOtaScraper('dev', new HttpBrowser($this->getHttpClient()));
        $result = $scraper->getEsp32Firmware();
        self::assertEquals('13.0.0.2', $result->getVersion());
        self::assertEquals('2023-07-16', $result->getPublishDate()->format('Y-m-d'));
    }

    private function getHttpClient(): HttpClientInterface
    {
        return new MockClient();
    }
}


class MockClient extends MockHttpClient
{
    private string $baseUri = 'https://ota.tasmota.com';

    public function __construct()
    {
        $callback = \Closure::fromCallable([$this, 'handleRequests']);

        parent::__construct($callback, $this->baseUri);
    }

    private function handleRequests(string $method, string $url): MockResponse
    {
        if ($method === 'GET' && $url === 'https://ota.tasmota.com/tasmota/release/') {
            return $this->getFixtureResponse('stable.html');
        }

        if ($method === 'GET' && $url === 'https://ota.tasmota.com/tasmota/') {
            return $this->getFixtureResponse('dev.html');
        }

        if ($method === 'GET' && $url === 'https://ota.tasmota.com/tasmota32/release/') {
            return $this->getFixtureResponse('stable_esp32.html');
        }

        if ($method === 'GET' && $url === 'https://ota.tasmota.com/tasmota32/') {
            return $this->getFixtureResponse('dev_esp32.html');
        }


        throw new \UnexpectedValueException("Mock not implemented: $method $url");
    }

    private function getFixtureResponse(string $fixture): MockResponse
    {
        return new MockResponse(
            TestUtils::loadFixture($fixture),
            ['http_code' => 200]
        );
    }
}
