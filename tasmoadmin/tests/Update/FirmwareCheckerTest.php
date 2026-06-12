<?php

namespace Tests\TasmoAdmin\Update;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use TasmoAdmin\Update\FirmwareChecker;

class FirmwareCheckerTest extends TestCase
{
    public function testIsValidSuccess(): void
    {
        $firmwareChecker = new FirmwareChecker($this->getClientWithResponse(new Response(200)));

        self::assertTrue($firmwareChecker->isValid('https://example.org/firmware.bin'));
    }

    public function testIsValidNotFound(): void
    {
        $firmwareChecker = new FirmwareChecker($this->getClientWithResponse(new Response(404)));

        self::assertFalse($firmwareChecker->isValid('https://example.org/firmware.bin'));
    }

    public function testIsValidConnectError(): void
    {
        $firmwareChecker = new FirmwareChecker($this->getClient(new GuzzleRejectMock(ConnectException::class, 'Server is down')));

        self::assertFalse($firmwareChecker->isValid('https://example.org/firmware.bin'));
    }

    public function testIsValidClientError(): void
    {
        $request = new Request('HEAD', 'https://example.org/firmware.bin');
        $mock = new MockHandler([
            new ClientException('Not found', $request, new Response(404)),
        ]);

        $firmwareChecker = new FirmwareChecker($this->getClient($mock));

        self::assertFalse($firmwareChecker->isValid('https://example.org/firmware.bin'));
    }

    public function testIsValidBubblesNonGuzzleExceptions(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects(self::once())
            ->method('head')
            ->with('https://example.org/firmware.bin')
            ->willThrowException(new \RuntimeException('unexpected failure'))
        ;

        $firmwareChecker = new FirmwareChecker($client);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('unexpected failure');

        $firmwareChecker->isValid('https://example.org/firmware.bin');
    }

    private function getClientWithResponse(?Response $response = null): Client
    {
        $responses = [];
        if ($response) {
            $responses[] = $response;
        }

        return $this->getClient(new MockHandler($responses));
    }

    private function getClient(callable $mock): Client
    {
        $handlerStack = HandlerStack::create($mock);

        return new Client(['handler' => $handlerStack]);
    }
}

class GuzzleRejectMock
{
    private string $exception;

    private string $message;

    public function __construct(string $exception, $message)
    {
        $this->exception = $exception;
        $this->message = $message;
    }

    public function __invoke(RequestInterface $request, array $options): RejectedPromise
    {
        return new RejectedPromise(new $this->exception($this->message, $request));
    }
}
