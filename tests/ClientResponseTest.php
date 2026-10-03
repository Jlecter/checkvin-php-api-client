<?php

declare(strict_types=1);

namespace CheckVin\Api\Tests;

use CheckVin\Api\Http\Response\ClientResponse;
use PHPUnit\Framework\TestCase;

final class ClientResponseTest extends TestCase
{
    public function testSuccessJsonBody(): void
    {
        // Arrange
        $body = (string) json_encode(['message' => 'Report found', 'preset_link' => 'https://example.com/hash']);

        // Action
        $response = ClientResponse::fromBody($body, 200);

        // Assert
        self::assertSame(200, $response->getResponseHttpCode());
        self::assertSame('Report found', $response->getData()['message']);
    }

    public function testErrorJsonBodyWith400(): void
    {
        // Arrange
        $body = (string) json_encode(['message' => 'Bad request', 'errors' => ['field' => 'required']]);

        // Action
        $response = ClientResponse::fromBody($body, 400);

        // Assert
        self::assertSame(400, $response->getResponseHttpCode());
        self::assertSame('Bad request', $response->getData()['message']);
        self::assertSame(['field' => 'required'], $response->getData()['errors']);
    }

    public function test404JsonBody(): void
    {
        // Arrange
        $body = (string) json_encode(['message' => 'Report not found', 'preset_link' => '']);

        // Action
        $response = ClientResponse::fromBody($body, 404);

        // Assert
        self::assertSame(404, $response->getResponseHttpCode());
        self::assertSame('Report not found', $response->getData()['message']);
    }

    public function testHtmlBodyReturnsMalformed(): void
    {
        // Arrange
        $body = '<html><body>Error page</body></html>';

        // Action
        $response = ClientResponse::fromBody($body, 200);

        // Assert
        self::assertSame(0, $response->getResponseHttpCode());
        self::assertSame('Malformed response body', $response->getData()['message']);
    }

    public function testEmptyBodyReturnsMalformed(): void
    {
        // Action
        $response = ClientResponse::fromBody('', 200);

        // Assert
        self::assertSame(0, $response->getResponseHttpCode());
        self::assertSame('Malformed response body', $response->getData()['message']);
    }
}
