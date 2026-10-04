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
        self::assertTrue($response->hasValidBody());
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
        self::assertTrue($response->hasValidBody());
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
        self::assertTrue($response->hasValidBody());
    }

    public function testHtmlBodyKeepsRealHttpCode(): void
    {
        // Arrange
        $body = '<html><body>Error page</body></html>';

        // Action
        $response = ClientResponse::fromBody($body, 200);

        // Assert
        self::assertSame(200, $response->getResponseHttpCode());
        self::assertFalse($response->hasValidBody());
        self::assertSame('Malformed response body (HTTP 200)', $response->getData()['message']);
    }

    public function testEmptyBodyKeepsRealHttpCode(): void
    {
        // Action
        $response = ClientResponse::fromBody('', 503);

        // Assert
        self::assertSame(503, $response->getResponseHttpCode());
        self::assertFalse($response->hasValidBody());
        self::assertSame('Malformed response body (HTTP 503)', $response->getData()['message']);
    }

    public function testMalformedBodyOnNon200PreservesHttpCode(): void
    {
        // Arrange
        $body = 'not json';

        // Action
        $response = ClientResponse::fromBody($body, 502);

        // Assert
        self::assertSame(502, $response->getResponseHttpCode());
        self::assertFalse($response->hasValidBody());
        self::assertSame('Malformed response body (HTTP 502)', $response->getData()['message']);
    }

    public function testJsonListBodyIsMalformed(): void
    {
        // Arrange — API contract requires a JSON object; a JSON list is invalid
        $body = (string) json_encode([1, 2, 3]);

        // Action
        $response = ClientResponse::fromBody($body, 200);

        // Assert
        self::assertFalse($response->hasValidBody());
        self::assertSame('Malformed response body (HTTP 200)', $response->getData()['message']);
    }

    public function testEmptyJsonArrayIsValidBody(): void
    {
        // Arrange — `[]` is the PHP equivalent of both `{}` and `[]` after json_decode;
        //            an empty response is treated as valid (no data, no error)
        $body = '[]';

        // Action
        $response = ClientResponse::fromBody($body, 200);

        // Assert
        self::assertTrue($response->hasValidBody());
        self::assertSame([], $response->getData());
    }

    public function testEmptyJsonObjectIsValidBody(): void
    {
        // Arrange — `{}` decodes to [] in PHP; must remain valid
        $body = '{}';

        // Action
        $response = ClientResponse::fromBody($body, 200);

        // Assert
        self::assertTrue($response->hasValidBody());
        self::assertSame([], $response->getData());
    }
}
