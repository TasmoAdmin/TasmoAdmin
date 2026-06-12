<?php

namespace Tests\TasmoAdmin\Helper;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use TasmoAdmin\Helper\TasmotaFirmware;
use TasmoAdmin\Helper\TasmotaFirmwareResult;
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

    public function testGetReleaseNotesTransformsContentAndIssueLinks(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects(self::once())
            ->method('get')
            ->with(self::stringContains('RELEASENOTES.md?r='))
            ->willReturn(new Response(200, [], "/*\n * Fixes #123\n */\n![logo](https://github.com/arendst/Tasmota/blob/master/tools/logo/TASMOTA_FullLogo_Vector.svg)\n"))
        ;

        $helper = new TasmotaHelper(
            new GithubFlavoredMarkdownConverter(),
            $client,
            $this->createMock(TasmotaOtaScraper::class),
            'stable'
        );

        $result = $helper->getReleaseNotes();

        self::assertStringContainsString("href='https://github.com/arendst/Tasmota/issues/123'", $result);
        self::assertStringContainsString(
            'https://raw.githubusercontent.com/arendst/Tasmota/master/tools/logo/TASMOTA_FullLogo_Vector.svg',
            $result
        );
    }

    public function testGetChangelogUsesStableChannelAndIssueLinks(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects(self::once())
            ->method('get')
            ->with(self::stringContains('https://raw.githubusercontent.com/arendst/Tasmota/master/CHANGELOG.md?r='))
            ->willReturn(new Response(200, [], 'See #456'))
        ;

        $helper = new TasmotaHelper(
            new GithubFlavoredMarkdownConverter(),
            $client,
            $this->createMock(TasmotaOtaScraper::class),
            'stable'
        );

        $result = $helper->getChangelog();

        self::assertStringContainsString("href='https://github.com/arendst/Tasmota/issues/456'", $result);
    }

    public function testGetChangelogUsesDevelopmentChannelUrl(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects(self::once())
            ->method('get')
            ->with(self::stringContains('https://raw.githubusercontent.com/arendst/Tasmota/development/CHANGELOG.md?r='))
            ->willReturn(new Response(200, [], 'See #789'))
        ;

        $helper = new TasmotaHelper(
            new GithubFlavoredMarkdownConverter(),
            $client,
            $this->createMock(TasmotaOtaScraper::class),
            'dev'
        );

        $result = $helper->getChangelog();

        self::assertStringContainsString("href='https://github.com/arendst/Tasmota/issues/789'", $result);
    }

    public function testGetReleaseNotesReturnsFailureMessageWhenRequestFails(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects(self::once())
            ->method('get')
            ->with(self::stringContains('https://raw.githubusercontent.com/arendst/Tasmota/development/RELEASENOTES.md?r='))
            ->willThrowException(new class('boom') extends \RuntimeException implements GuzzleException {})
        ;

        $helper = new TasmotaHelper(
            new GithubFlavoredMarkdownConverter(),
            $client,
            $this->createMock(TasmotaOtaScraper::class),
            'stable'
        );

        $result = $helper->getReleaseNotes();

        self::assertStringContainsString('Failed to load ', $result);
        self::assertStringContainsString('https://raw.githubusercontent.com/arendst/Tasmota/development/RELEASENOTES.md?r=', $result);
        self::assertStringContainsString('boom', $result);
    }

    public function testGetReleasesReturnsSortedUniqueNamesAcrossArchitectures(): void
    {
        $scraper = $this->createMock(TasmotaOtaScraper::class);
        $scraper->expects(self::once())
            ->method('getEsp8266Firmware')
            ->willReturn(new TasmotaFirmwareResult(
                'v14.0.0',
                new \DateTime('2026-06-11T00:00:00+00:00'),
                [
                    new TasmotaFirmware('tasmota-display.bin.gz', 'https://ota/tasmota-display.bin.gz'),
                    new TasmotaFirmware('tasmota.bin.gz', 'https://ota/tasmota.bin.gz'),
                    new TasmotaFirmware('tasmota-display.bin.gz', 'https://ota/tasmota-display-dup.bin.gz'),
                    new TasmotaFirmware('tasmota-minimal.bin.gz', 'https://ota/tasmota-minimal.bin.gz'),
                    new TasmotaFirmware('notes.txt', 'https://ota/notes.txt'),
                ]
            ))
        ;
        $scraper->expects(self::once())
            ->method('getEsp32Firmware')
            ->willReturn(new TasmotaFirmwareResult(
                'v14.0.0',
                new \DateTime('2026-06-11T00:00:00+00:00'),
                [
                    new TasmotaFirmware('tasmota32-zigbee.bin', 'https://ota/tasmota32-zigbee.bin'),
                    new TasmotaFirmware('tasmota-display.bin', 'https://ota/tasmota-display.bin'),
                    new TasmotaFirmware('tasmota32-zigbee.bin', 'https://ota/tasmota32-zigbee-dup.bin'),
                    new TasmotaFirmware('readme.md', 'https://ota/readme.md'),
                ]
            ))
        ;

        $helper = new TasmotaHelper(
            new GithubFlavoredMarkdownConverter(),
            $this->createMock(Client::class),
            $scraper,
            'stable'
        );

        self::assertSame(
            ['tasmota', 'tasmota-display', 'tasmota32-zigbee'],
            $helper->getReleases()
        );
    }

    public function testGetLatestFirmwaresReturnsEsp8266FirmwareAndMinimalFirmware(): void
    {
        $scraper = $this->createMock(TasmotaOtaScraper::class);
        $scraper->expects(self::once())
            ->method('getEsp8266Firmware')
            ->willReturn(new TasmotaFirmwareResult(
                'v14.0.0',
                new \DateTime('2026-06-11T00:00:00+00:00'),
                [
                    new TasmotaFirmware('tasmota-minimal.bin.gz', 'https://ota/minimal.bin.gz'),
                    new TasmotaFirmware('tasmota-sensors.bin.gz', 'https://ota/tasmota-sensors.bin.gz'),
                ]
            ))
        ;

        $helper = new TasmotaHelper(
            new GithubFlavoredMarkdownConverter(),
            $this->createMock(Client::class),
            $scraper,
            'stable'
        );

        $result = $helper->getLatestFirmwares('tasmota-sensors.bin');

        self::assertSame('https://ota/tasmota-sensors.bin.gz', $result->getFirmwareUrl());
        self::assertTrue($result->hasMinimalFirmware());
        self::assertSame('https://ota/minimal.bin.gz', $result->getMinimalFirmwareUrl());
        self::assertSame('v14.0.0', $result->getTagName());
    }

    public function testGetLatestFirmwaresReturnsEsp32FirmwareWithoutMinimalFirmware(): void
    {
        $scraper = $this->createMock(TasmotaOtaScraper::class);
        $scraper->expects(self::once())
            ->method('getEsp32Firmware')
            ->willReturn(new TasmotaFirmwareResult(
                'v14.0.0',
                new \DateTime('2026-06-11T00:00:00+00:00'),
                [
                    new TasmotaFirmware('tasmota32.bin', 'https://ota/tasmota32.bin'),
                ]
            ))
        ;

        $helper = new TasmotaHelper(
            new GithubFlavoredMarkdownConverter(),
            $this->createMock(Client::class),
            $scraper,
            'stable'
        );

        $result = $helper->getLatestFirmwares('tasmota32.bin');

        self::assertSame('https://ota/tasmota32.bin', $result->getFirmwareUrl());
        self::assertFalse($result->hasMinimalFirmware());
        self::assertNull($result->getMinimalFirmwareUrl());
    }

    public function testGetLatestFirmwaresResolvesNonDefaultEsp32Variant(): void
    {
        $scraper = $this->createMock(TasmotaOtaScraper::class);
        $scraper->expects(self::once())
            ->method('getEsp32Firmware')
            ->willReturn(new TasmotaFirmwareResult(
                'v14.0.0',
                new \DateTime('2026-06-11T00:00:00+00:00'),
                [
                    new TasmotaFirmware('tasmota32solo1.bin', 'https://ota/tasmota32solo1.bin'),
                ]
            ))
        ;

        $helper = new TasmotaHelper(
            new GithubFlavoredMarkdownConverter(),
            $this->createMock(Client::class),
            $scraper,
            'stable'
        );

        $result = $helper->getLatestFirmwares('tasmota32solo1.bin');

        self::assertSame('https://ota/tasmota32solo1.bin', $result->getFirmwareUrl());
        self::assertFalse($result->hasMinimalFirmware());
    }

    public function testGetLatestFirmwaresThrowsWhenConfiguredFirmwareCannotBeResolved(): void
    {
        $scraper = $this->createMock(TasmotaOtaScraper::class);
        $scraper->expects(self::once())
            ->method('getEsp8266Firmware')
            ->willReturn(new TasmotaFirmwareResult(
                'v14.0.0',
                new \DateTime('2026-06-11T00:00:00+00:00'),
                [
                    new TasmotaFirmware('tasmota-minimal.bin.gz', 'https://ota/minimal.bin.gz'),
                ]
            ))
        ;

        $helper = new TasmotaHelper(
            new GithubFlavoredMarkdownConverter(),
            $this->createMock(Client::class),
            $scraper,
            'stable'
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Failed to resolve firmware');

        $helper->getLatestFirmwares('tasmota-sensors.bin');
    }

    public function testGetLatestFirmwaresThrowsWhenMinimalFirmwareIsMissing(): void
    {
        $scraper = $this->createMock(TasmotaOtaScraper::class);
        $scraper->expects(self::once())
            ->method('getEsp8266Firmware')
            ->willReturn(new TasmotaFirmwareResult(
                'v14.0.0',
                new \DateTime('2026-06-11T00:00:00+00:00'),
                [
                    new TasmotaFirmware('tasmota-sensors.bin.gz', 'https://ota/tasmota-sensors.bin.gz'),
                ]
            ))
        ;

        $helper = new TasmotaHelper(
            new GithubFlavoredMarkdownConverter(),
            $this->createMock(Client::class),
            $scraper,
            'stable'
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Failed to resolve firmware');

        $helper->getLatestFirmwares('tasmota-sensors.bin');
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
