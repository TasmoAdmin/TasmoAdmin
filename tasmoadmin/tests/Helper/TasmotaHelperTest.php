<?php

namespace Tests\TasmoAdmin\Helper;

use GuzzleHttp\Client;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use TasmoAdmin\Helper\TasmotaHelper;
use TasmoAdmin\Helper\TasmotaOtaScraper;
use Tests\TasmoAdmin\TestUtils;

class TasmotaHelperTest extends TestCase
{
    public function testGetEsp8266ReleasesExcludesEsp32Assets(): void
    {
        $helper = $this->createHelper();

        self::assertContains('tasmota-sensors', $helper->getEsp8266Releases());
        self::assertNotContains('tasmota32', $helper->getEsp8266Releases());
    }

    public function testGetEsp32ReleasesExcludesEsp8266Assets(): void
    {
        $helper = $this->createHelper();

        self::assertContains('tasmota32', $helper->getEsp32Releases());
        self::assertNotContains('tasmota-sensors', $helper->getEsp32Releases());
    }

    private function createHelper(): TasmotaHelper
    {
        return new TasmotaHelper(
            new GithubFlavoredMarkdownConverter(),
            new Client(),
            new TasmotaOtaScraper('stable', new HttpBrowser(new TasmotaHelperMockClient())),
            'stable'
        );
    }
}

class TasmotaHelperMockClient extends MockHttpClient
{
    private string $baseUri = 'https://ota.tasmota.com';

    public function __construct()
    {
        $callback = \Closure::fromCallable([$this, 'handleRequests']);

        parent::__construct($callback, $this->baseUri);
    }

    private function handleRequests(string $method, string $url): MockResponse
    {
        if ('GET' === $method && 'https://ota.tasmota.com/tasmota/release/' === $url) {
            return $this->getFixtureResponse('stable.html');
        }

        if ('GET' === $method && 'https://ota.tasmota.com/tasmota32/release/' === $url) {
            return $this->getFixtureResponse('stable_esp32.html');
        }

        throw new \UnexpectedValueException("Mock not implemented: {$method} {$url}");
    }

    private function getFixtureResponse(string $fixture): MockResponse
    {
        return new MockResponse(
            TestUtils::loadFixture($fixture),
            ['http_code' => 200]
        );
    }
}
