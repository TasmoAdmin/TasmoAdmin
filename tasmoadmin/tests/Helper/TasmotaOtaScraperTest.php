<?php

namespace Tests\TasmoAdmin\Helper;

use Goutte\Client;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use TasmoAdmin\Helper\TasmotaOtaScraper;
use PHPUnit\Framework\TestCase;
use Tests\TasmoAdmin\TestUtils;

class TasmotaOtaScraperTest extends TestCase
{
    public function testGetStableFirmware(): void
    {
        $scraper = new TasmotaOtaScraper('stable', new Client($this->getHttpClient()));
        $result = $scraper->getFirmware();
        self::assertEquals('12.1.1', $result->getVersion());

    }

    public function testGetDevFirmware(): void
    {
        $scraper = new TasmotaOtaScraper('dev', new Client($this->getHttpClient()));
        $result = $scraper->getFirmware();
        self::assertEquals('12.1.1.1', $result->getVersion());
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
        if ($method === 'GET' && str_starts_with($url, $this->baseUri.'/tasmota/release/')) {
            return $this->getStable();
        }

        if ($method === 'GET' && str_starts_with($url, $this->baseUri.'/tasmota')) {
            return $this->getDev();
        }

        throw new \UnexpectedValueException("Mock not implemented: $method/$url");
    }


    private function getStable(): MockResponse
    {
        return new MockResponse(
            TestUtils::loadFixture('stable.html'),
            ['http_code' => 200]
        );
    }

    private function getDev(): MockResponse
    {
        return new MockResponse(
            TestUtils::loadFixture('dev.html'),
            ['http_code' => 200]
        );
    }
}
