<?php

declare(strict_types=1);

namespace CheckVin\Api\Tests;

use CheckVin\Api\Config\Config;
use CheckVin\Api\Exception\RequestFailed;
use CheckVin\Api\Http\Client\Client;
use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase
{
    public function testConnectionRefusedThrowsRequestFailed(): void
    {
        // Arrange
        $client = new Client(new Config('http://127.0.0.1:' . self::closedPort(), connectTimeoutMs: 300));

        // Action + Assert
        $this->expectException(RequestFailed::class);
        $client->request('/anything', []);
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
}
