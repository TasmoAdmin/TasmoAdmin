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

    public function download(string $url): void
    {
        $this->client->get($url, ['sink' => $this->path . basename($url)]);
    }
}
