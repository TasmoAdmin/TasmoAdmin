<?php

namespace TasmoAdmin\Update;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class FirmwareChecker
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function isValid(string $url): bool
    {
        try {
            $this->client->head($url);
        } catch (GuzzleException $exception) {
            return false;
        }

        return true;
    }
}
