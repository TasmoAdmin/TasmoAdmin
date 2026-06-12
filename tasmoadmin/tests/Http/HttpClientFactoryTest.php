<?php

namespace Tests\TasmoAdmin\Http;

use GuzzleHttp\Client;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use TasmoAdmin\Config;
use TasmoAdmin\Http\HttpClientFactory;

class HttpClientFactoryTest extends TestCase
{
    public function testGetClientUsesConfiguredTimeouts(): void
    {
        $root = vfsStream::setup('http-client-factory-'.bin2hex(random_bytes(4)));
        $config = new Config($root->url().'/', $root->url().'/');
        $config->writeAll([
            'connect_timeout' => '7',
            'timeout' => '9',
        ]);

        $factory = new HttpClientFactory($config);
        $client = $factory->getClient();

        $clientConfig = $this->readClientConfig($client);

        self::assertSame(7, $clientConfig['connect_timeout']);
        self::assertSame(9, $clientConfig['timeout']);
    }

    public function testGetClientUsesDefaultTimeoutsWhenConfigIsUnchanged(): void
    {
        $root = vfsStream::setup('http-client-factory-defaults-'.bin2hex(random_bytes(4)));
        $config = new Config($root->url().'/', $root->url().'/');

        $factory = new HttpClientFactory($config);
        $client = $factory->getClient();

        $clientConfig = $this->readClientConfig($client);

        self::assertSame(5, $clientConfig['connect_timeout']);
        self::assertSame(5, $clientConfig['timeout']);
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
