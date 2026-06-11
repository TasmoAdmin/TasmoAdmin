<?php

namespace Tests\TasmoAdmin\Helper;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use PHPUnit\Framework\TestCase;
use TasmoAdmin\Helper\TasmoAdminHelper;

class TasmoAdminHelperTest extends TestCase
{
    public function testGetChangelogFetchesReleasesAndConvertsMarkdown(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects(self::once())
            ->method('get')
            ->with('https://api.github.com/repos/TasmoAdmin/TasmoAdmin/releases')
            ->willReturn(new Response(200, [], json_encode([
                ['body' => '# Release 1'],
                ['body' => 'Plain text'],
            ], JSON_THROW_ON_ERROR)))
        ;

        $helper = new TasmoAdminHelper(new GithubFlavoredMarkdownConverter(), $client);

        $releases = $helper->getChangelog();

        self::assertCount(2, $releases);
        self::assertStringContainsString('<h1>Release 1</h1>', (string) $releases[0]->body);
        self::assertStringContainsString('<p>Plain text</p>', (string) $releases[1]->body);
    }
}
