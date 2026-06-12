<?php

namespace Tests\TasmoAdmin\Update;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use TasmoAdmin\Update\UpdateChecker;
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

    public function testCheckForUpdateDevAlwaysFlagsUpdate(): void
    {
        $updateChecker = new UpdateChecker('dev', 'v1.8.0', $this->getClient(
            new Response(200, [], TestUtils::loadFixture('latest.json'))
        ));

        $result = $updateChecker->checkForUpdate();

        self::assertTrue($result['update']);
        self::assertSame('v1.8.0', $result['latest_tag']);
    }

    public function testCheckForUpdateBetaUsesReleaseListResponseShape(): void
    {
        $payload = json_encode([
            json_decode(TestUtils::loadFixture('latest.json'), true, 512, JSON_THROW_ON_ERROR),
        ], JSON_THROW_ON_ERROR);

        $updateChecker = new UpdateChecker('beta', 'v1.8.0', $this->getClient(
            new Response(200, [], $payload)
        ));

        $result = $updateChecker->checkForUpdate();

        self::assertFalse($result['update']);
        self::assertFalse($result['error']);
        self::assertSame('v1.8.0', $result['latest_tag']);
        self::assertSame(
            'https://github.com/TasmoAdmin/TasmoAdmin/releases/download/v1.8.0/tasmoadmin_v1.8.0.zip',
            $result['release_url']
        );
    }

    public function testCheckForUpdateReturnsCurlErrorOnRequestFailure(): void
    {
        $request = new Request('GET', 'https://api.github.com/repos/TasmoAdmin/TasmoAdmin/releases/latest');
        $mock = new MockHandler([
            new RequestException('network down', $request),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $updateChecker = new UpdateChecker('stable', 'v1.0.0', $client);
        $result = $updateChecker->checkForUpdate();

        self::assertFalse($result['update']);
        self::assertTrue($result['error']);
        self::assertSame('SELFUPDATE_ERROR_CURL:  - network down', $result['msg']);
    }

    public function testCheckForUpdateReturnsDownloadMissingWhenAssetsAreIncomplete(): void
    {
        $payload = json_encode([
            'tag_name' => 'v1.9.0',
            'assets' => [
                ['browser_download_url' => 'https://example.com/only-one.zip'],
            ],
        ], JSON_THROW_ON_ERROR);

        $updateChecker = new UpdateChecker('stable', 'v1.8.0', $this->getClient(
            new Response(200, [], $payload)
        ));
        $result = $updateChecker->checkForUpdate();

        self::assertFalse($result['update']);
        self::assertTrue($result['error']);
        self::assertSame('SELFUPDATE_DOWNLOAD_MISSING: ', $result['msg']);
        self::assertSame('v1.9.0', $result['latest_tag']);
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
