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
        $this->assertValid($result);
    }

    public function testCheckForUpdateStableNoUpdate(): void
    {
        $updateChecker = new UpdateChecker('stable', 'v1.8.0', $this->getClient(
            new Response(200, [], TestUtils::loadFixture('latest.json'))
        ));
        $result = $updateChecker->checkForUpdate();
        self::assertFalse($result['update']);
    }

    public function testCheckForUpdateBetaUpgrade(): void
    {
        $updateChecker = new UpdateChecker('stable', 'v1.8.0-beta1', $this->getClient(
            new Response(200, [], TestUtils::loadFixture('latest.json'))
        ));
        $result = $updateChecker->checkForUpdate();
        $this->assertValid($result);
    }

    public function testCheckForUpdateEmptyCurrentTag(): void
    {
        $updateChecker = new UpdateChecker('stable', '', $this->getClient(
            new Response(200, [], TestUtils::loadFixture('latest.json'))
        ));
        $result = $updateChecker->checkForUpdate();
        $this->assertValid($result);
    }

    private function assertValid(array $result): void
    {
        self::assertTrue($result['update']);
        self::assertEquals('v1.8.0', $result['latest_tag']);
        self::assertEquals('https://github.com/TasmoAdmin/TasmoAdmin/releases/download/v1.8.0/tasmoadmin_v1.8.0.zip', $result['release_url']);
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
