<?php

namespace TasmoAdmin\Helper;

use GuzzleHttp\Client;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class TasmoAdminHelper
{
    private GithubFlavoredMarkdownConverter $markDownParser;

    private Client $client;

    public function __construct(GithubFlavoredMarkdownConverter $markDownParser, Client $client)
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
            $changelog[$key]->body = $this->markDownParser->convert($value->body);
        }

        return $changelog;
    }
}
