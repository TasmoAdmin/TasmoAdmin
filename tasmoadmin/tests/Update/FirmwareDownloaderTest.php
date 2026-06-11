<?php

namespace Tests\TasmoAdmin\Update;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use TasmoAdmin\Update\FirmwareDownloader;

class FirmwareDownloaderTest extends TestCase
{
    public function testDownloadUsesSinkPathBasedOnFilename(): void
    {
        $client = $this->createMock(Client::class);
        $downloader = new FirmwareDownloader($client, '/tmp/downloads/');

        $client->expects(self::once())
            ->method('get')
            ->with(
                'https://example.com/firmware/tasmota.bin',
                ['sink' => '/tmp/downloads/tasmota.bin']
            )
        ;

        self::assertSame(
            '/tmp/downloads/tasmota.bin',
            $downloader->download('https://example.com/firmware/tasmota.bin')
        );
    }
}
