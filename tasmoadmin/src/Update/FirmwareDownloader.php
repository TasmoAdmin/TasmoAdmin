<?php

namespace TasmoAdmin\Update;

use GuzzleHttp\Client;

class FirmwareDownloader
{
    private Client $client;

    private string $path;

    public function __construct(Client $client, string $path)
    {
        $this->client = $client;
        $this->path = $path;
    }

    public function download(string $url): string
    {
        $downloadedPath = $this->path . basename($url);
        $this->client->get($url, ['sink' => $downloadedPath]);

        return $downloadedPath;
    }
}
