<?php

namespace Tests\TasmoAdmin\Update;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use TasmoAdmin\Update\UpdateChecker;
use PHPUnit\Framework\TestCase;
use Tests\TasmoAdmin\TestUtils;

class UpdateCheckerTest extends TestCase
{
    public function testCheckForUpdateDocker(): void
    {
        $updateChecker = new UpdateChecker('docker', 'v1.0.0', $this->getClient());
        $result = $updateChecker->checkForUpdate();
        self::assertFalse($result['update']);
    }

    public function testCheckForUpdateStableUpdate(): void
    {
        $updateChecker = new UpdateChecker('stable', 'v1.7.0', $this->getClient(
            new Response(200, [], TestUtils::loadFixture('latest.json'))
        ));
        $result = $updateChecker->checkForUpdate();
        self::assertTrue($result['update']);
    }

    public function testCheckForUpdateStableNoUpdate(): void
    {
        $updateChecker = new UpdateChecker('stable', 'v1.8.0', $this->getClient(
            new Response(200, [], TestUtils::loadFixture('latest.json'))
        ));
        $result = $updateChecker->checkForUpdate();
        self::assertFalse($result['update']);
    }

    private function getClient(?Response $response = null): Client
    {
        $responses = [];
        if ($response) {
            $responses[] = $response;
        }
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        return new Client(['handler' => $handlerStack]);
    }
}
