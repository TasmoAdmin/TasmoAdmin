<?php

namespace TasmoAdmin\Http;

use GuzzleHttp\Client;
use TasmoAdmin\Config;

class HttpClientFactory
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function getClient(): Client
    {
        return new Client([
            'connect_timeout' => $this->config->getConnectTimeout(),
            'timeout' => $this->config->getTimeout(),
        ]);
    }
}
