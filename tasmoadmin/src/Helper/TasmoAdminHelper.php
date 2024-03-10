<?php

namespace TasmoAdmin\Helper;

use GuzzleHttp\Client;

class TasmoAdminHelper
{
    private \Parsedown $markDownParser;

    private Client $client;

    public function __construct(\Parsedown $markDownParser, Client $client)
    {
        $this->markDownParser = $markDownParser;
        $this->client = $client;
    }

    public function getChangelog(): array
    {
        $changelogUrl = 'https://api.github.com/repos/TasmoAdmin/TasmoAdmin/releases';
        $changelogContent = $this->client->get($changelogUrl)->getBody()->getContents();

        $changelog = json_decode($changelogContent);

        foreach ($changelog as $key => $value) {
            $changelog[$key]->body = $this->markDownParser->parse($value->body);
        }

        return $changelog;
    }
}
