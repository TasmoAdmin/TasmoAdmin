<?php

namespace Tests\TasmoAdmin\Helper;

use GuzzleHttp\Client;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use TasmoAdmin\Config;
use TasmoAdmin\Helper\GuzzleFactory;

class GuzzleFactoryTest extends TestCase
{
    public function testGetClientSetsVersionedUserAgent(): void
    {
        $root = vfsStream::setup('guzzle-factory-'.bin2hex(random_bytes(4)));
        $config = new Config($root->url().'/', $root->url().'/');
        $config->write('current_git_tag', 'v5.1.0');

        $client = GuzzleFactory::getClient($config);

        self::assertSame('TasmoAdmin/v5.1.0', $this->readClientConfig($client)['headers']['User-Agent']);
    }

    /**
     * @return array<string, mixed>
     */
    private function readClientConfig(Client $client): array
    {
        return \Closure::bind(function () {
            return $this->config;
        }, $client, Client::class)();
    }
}
