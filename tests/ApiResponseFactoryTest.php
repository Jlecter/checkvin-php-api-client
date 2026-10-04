<?php

declare(strict_types=1);

namespace CheckVin\Api\Tests;

use CheckVin\Api\Http\Response\ApiResponseFactory;
use CheckVin\Api\Http\Response\ClientResponse;
use PHPUnit\Framework\TestCase;

final class ApiResponseFactoryTest extends TestCase
{
    public function testHttp200WithValidBodyIsSuccess(): void
    {
        // Arrange
        $raw = new ClientResponse(['message' => 'Report found'], 200);

        // Action
        $response = ApiResponseFactory::fromClientResponse($raw);

        // Assert
        self::assertTrue($response->isSuccess());
        self::assertNull($response->getError());
        self::assertSame('Report found', $response->getData()['message']);
    }

    public function testHttp200WithInvalidBodyIsError(): void
    {
        // Arrange — valid body flag is false, simulates a 200 HTML response
        $raw = ClientResponse::fromBody('<html>error</html>', 200);

        // Action
        $response = ApiResponseFactory::fromClientResponse($raw);

        // Assert
        self::assertFalse($response->isSuccess());
        self::assertStringContainsString('Malformed response body', $response->getError()->getMessage());
    }

    public function testNon200WithValidBodyIsError(): void
    {
        // Arrange
        $raw = new ClientResponse(['message' => 'Bad request', 'errors' => ['field' => 'required']], 400);

        // Action
        $response = ApiResponseFactory::fromClientResponse($raw);

        // Assert
        self::assertFalse($response->isSuccess());
        self::assertStringContainsString('Bad request', $response->getError()->getMessage());
        self::assertStringContainsString('required', $response->getError()->getMessage());
    }

    public function testNon200WithInvalidBodyIsError(): void
    {
        // Arrange
        $raw = ClientResponse::fromBody('not json', 503);

        // Action
        $response = ApiResponseFactory::fromClientResponse($raw);

        // Assert
        self::assertFalse($response->isSuccess());
        self::assertStringContainsString('Malformed response body (HTTP 503)', $response->getError()->getMessage());
    }
}
