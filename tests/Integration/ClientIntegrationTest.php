<?php

declare(strict_types=1);

namespace CheckVin\Api\Tests\Integration;

use CheckVin\Api\Config\Config;
use CheckVin\Api\Exception\RequestFailed;
use CheckVin\Api\Http\Client\Client;
use CheckVin\Api\Http\Response\ApiResponse;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('integration')]
final class ClientIntegrationTest extends TestCase
{
    private static int $port;
    /** @var resource|null */
    private static $serverProcess = null;

    public static function setUpBeforeClass(): void
    {
        self::$port = self::findFreePort();

        $descriptor = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $script = dirname(__DIR__) . '/Fixtures/server.php';

        self::$serverProcess = proc_open(
            [PHP_BINARY, '-S', sprintf('127.0.0.1:%d', self::$port), $script],
            $descriptor,
            $pipes,
        );

        self::waitForPort(self::$port);
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$serverProcess !== null) {
            proc_terminate(self::$serverProcess);
            proc_close(self::$serverProcess);
            self::$serverProcess = null;
        }
    }

    public function testConnectionRefusedThrowsRequestFailed(): void
    {
        // Arrange
        $client = new Client(new Config('http://127.0.0.1:' . self::closedPort(), connectTimeoutMs: 300));

        // Action + Assert
        $this->expectException(RequestFailed::class);
        $client->request('/anything', []);
    }

    public function testSlowEndpointThrowsRequestFailed(): void
    {
        // Arrange — 300 ms total timeout, server sleeps 1 s; connect timeout must be ≤ total
        $client = new Client(new Config($this->baseUrl(), connectTimeoutMs: 100, timeoutMs: 300));

        // Action + Assert
        $this->expectException(RequestFailed::class);
        $client->request('/slow', []);
    }

    public function test200JsonEndToEnd(): void
    {
        // Arrange
        $client = new Client(new Config($this->baseUrl()));

        // Action
        $raw = $client->request('/200-json', []);
        $response = ApiResponse::fromClientResponse($raw);

        // Assert
        self::assertTrue($response->isSuccess());
        self::assertSame('Report found', $response->getData()['message']);
        self::assertNull($response->getError());
    }

    private function baseUrl(): string
    {
        return sprintf('http://127.0.0.1:%d', self::$port);
    }

    private static function closedPort(): int
    {
        $sock = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);

        if ($sock === false) {
            throw new \RuntimeException(sprintf('Cannot bind ephemeral port: %s', $errstr));
        }

        $name = stream_socket_get_name($sock, false);
        fclose($sock);

        return (int) substr($name, strrpos($name, ':') + 1);
    }

    private static function findFreePort(): int
    {
        $sock = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);

        if ($sock === false) {
            throw new \RuntimeException(sprintf('Cannot bind ephemeral port: %s', $errstr));
        }

        $name = stream_socket_get_name($sock, false);
        fclose($sock);

        $port = (int) substr($name, strrpos($name, ':') + 1);

        if ($port === 0) {
            throw new \RuntimeException('Failed to extract free port from socket name: ' . $name);
        }

        return $port;
    }

    private static function waitForPort(int $port): void
    {
        $deadline = microtime(true) + 2.0;

        while (microtime(true) < $deadline) {
            $sock = @stream_socket_client(sprintf('tcp://127.0.0.1:%d', $port), $errno, $errstr, 0.05);

            if ($sock !== false) {
                fclose($sock);
                return;
            }

            usleep(50_000);
        }

        throw new \RuntimeException(sprintf('Server did not start on port %d within 2 seconds', $port));
    }
}
