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

    public function getChangelog(): string
    {
        $changeLogUrl = "https://raw.githubusercontent.com/TasmoAdmin/TasmoAdmin/master/CHANGELOG.md?r=" . time();

        $changeLog = $this->client->get($changeLogUrl)->getBody()->getContents();

        return $this->markDownParser->parse($changeLog);
    }
}
