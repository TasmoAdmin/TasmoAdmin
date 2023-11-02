<?php

namespace TasmoAdmin\Helper;

use GuzzleHttp\Client;
use Parsedown;

class TasmoAdminHelper
{
    private Parsedown $markDownParser;

    private Client $client;

    public function __construct(Parsedown $markDownParser, Client $client)
    {
        $this->markDownParser = $markDownParser;
        $this->client = $client;
    }

    public function getChangelog(): array
    {
        $changeLogUrl = "https://api.github.com/repos/TasmoAdmin/TasmoAdmin/releases";
        $changeLogJSON = $this->client->get($changeLogUrl)->getBody()->getContents();

        $tmpArray = json_decode($changeLogJSON);

        foreach($tmpArray as $key => $value) {
            $tmpArray[$key]->body = $this->markDownParser->parse($value->body);
        }

        return $tmpArray;
    }
}
