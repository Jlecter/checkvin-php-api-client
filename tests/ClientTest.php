<?php

declare(strict_types=1);

namespace CheckVin\Api\Tests;

use CheckVin\Api\Config\Config;
use CheckVin\Api\Exception\RequestFailed;
use CheckVin\Api\Http\Client\Client;
use CheckVin\Api\Http\Response\ClientResponse;
use CheckVin\Api\Http\Response\Success\ApplicationSuccessResponse;
use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase
{
    public function testConnectionRefusedThrowsRequestFailed(): void
    {
        // Arrange
        $client = new Client(new Config('http://127.0.0.1:19999', connectTimeoutMs: 300));

        // Action + Assert
        $this->expectException(RequestFailed::class);
        $client->request('/anything', []);
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

    public function testMakeResponseReturns200AsSuccess(): void
    {
        // Arrange
        $client = new Client(new Config('http://127.0.0.1:19999'));
        $raw = new ClientResponse(['message' => 'Report found'], ApplicationSuccessResponse::SUCCESS_CODE);

        // Action
        $response = $client->makeResponse($raw);

        // Assert
        self::assertTrue($response->isSuccess());
        self::assertNull($response->getError());
        self::assertSame('Report found', $response->getData()['message']);
    }

    public function testMakeResponseReturnsNon200AsError(): void
    {
        // Arrange
        $client = new Client(new Config('http://127.0.0.1:19999'));
        $raw = new ClientResponse(['message' => 'Bad request', 'errors' => ['field' => 'required']], 400);

        // Action
        $response = $client->makeResponse($raw);

        // Assert
        self::assertFalse($response->isSuccess());
        self::assertStringContainsString('Bad request', $response->getError()->getMessage());
        self::assertStringContainsString('required', $response->getError()->getMessage());
    }
}
