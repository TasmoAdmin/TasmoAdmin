<?php

namespace TasmoAdmin\Helper;

use GuzzleHttp\Client;
use TasmoAdmin\Config;

class GuzzleFactory
{
    public static function getClient(Config $config): Client
    {
        return new Client(['headers' => [
            'User-Agent' => "TasmoAdmin/{$config->read('current_git_tag')}",
        ]]);
    }
}
