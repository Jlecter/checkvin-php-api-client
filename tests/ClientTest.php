<?php

declare(strict_types=1);

namespace CheckVin\Api\Tests;

use CheckVin\Api\Config\Config;
use CheckVin\Api\Exception\RequestFailed;
use CheckVin\Api\Http\Client\Client;
use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase
{
    private static int $port = 18742;
    /** @var resource|null */
    private static $serverProcess = null;

    public static function setUpBeforeClass(): void
    {
        $serverScript = __DIR__ . '/Fixtures/server.php';

        $descriptor = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        self::$serverProcess = proc_open(
            sprintf('php -S 127.0.0.1:%d %s', self::$port, $serverScript),
            $descriptor,
            $pipes,
        );

        // Give the server a moment to bind the port
        usleep(200_000);
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$serverProcess !== null) {
            proc_terminate(self::$serverProcess);
            proc_close(self::$serverProcess);
            self::$serverProcess = null;
        }
    }

    private function baseUrl(): string
    {
        return sprintf('http://127.0.0.1:%d', self::$port);
    }

    private function makeClient(?Config $config = null): Client
    {
        return new Client($config ?? new Config($this->baseUrl()));
    }

    public function testSuccessJsonResponseIsSuccess(): void
    {
        // Arrange
        $client = $this->makeClient();

        // Action
        $raw = $client->request('/200-json', []);
        $response = $client->makeResponse($raw);

        // Assert
        self::assertTrue($response->isSuccess());
        self::assertSame('Report found', $response->getData()['message']);
        self::assertNull($response->getError());
    }

    public function testNon200JsonResponseIsError(): void
    {
        // Arrange
        $client = $this->makeClient();

        // Action
        $raw = $client->request('/400-json', []);
        $response = $client->makeResponse($raw);

        // Assert
        self::assertFalse($response->isSuccess());
        self::assertStringContainsString('Bad request', $response->getError()->getMessage());
        self::assertStringContainsString('required', $response->getError()->getMessage());
    }

    public function test404JsonResponseIsError(): void
    {
        // Arrange
        $client = $this->makeClient();

        // Action
        $raw = $client->request('/404-json', []);
        $response = $client->makeResponse($raw);

        // Assert
        self::assertFalse($response->isSuccess());
        self::assertSame('Report not found', $response->getError()->getMessage());
    }

    public function testHtmlBodyOn200IsErrorWithoutTypeError(): void
    {
        // Arrange
        $client = $this->makeClient();

        // Action
        $raw = $client->request('/200-html', []);
        $response = $client->makeResponse($raw);

        // Assert
        self::assertFalse($response->isSuccess());
        self::assertSame('Malformed response body', $response->getError()->getMessage());
    }

    public function testEmptyBodyIsError(): void
    {
        // Arrange
        $client = $this->makeClient();

        // Action
        $raw = $client->request('/empty', []);
        $response = $client->makeResponse($raw);

        // Assert
        self::assertFalse($response->isSuccess());
        self::assertSame('Malformed response body', $response->getError()->getMessage());
    }

    public function testConnectionRefusedThrowsRequestFailed(): void
    {
        // Arrange
        $client = new Client(new Config('http://127.0.0.1:19999', connectTimeoutMs: 300));

        // Action + Assert
        $this->expectException(RequestFailed::class);
        $client->request('/anything', []);
    }

    public function testSlowEndpointThrowsRequestFailed(): void
    {
        // Arrange — 300 ms timeout, server sleeps 5 s
        $client = $this->makeClient(new Config($this->baseUrl(), timeoutMs: 300));

        // Action + Assert
        $this->expectException(RequestFailed::class);
        $client->request('/slow', []);
    }

    public function testHostFromConfigIsUsed(): void
    {
        // Arrange
        $wrongHost = new Config('http://127.0.0.1:19998', connectTimeoutMs: 300);
        $client = new Client($wrongHost);

        // Action + Assert
        $this->expectException(RequestFailed::class);
        $client->request('/200-json', []);
    }
}
