<?php

declare(strict_types=1);

namespace CheckVin\Api\Tests;

use CheckVin\Api\Http\Data\Error;
use CheckVin\Api\Http\Response\ClientResponse;
use PHPUnit\Framework\TestCase;

final class ErrorTest extends TestCase
{
    public function testNormalMessageIsReturned(): void
    {
        // Arrange
        $response = new ClientResponse(['message' => 'Something went wrong'], 400);

        // Action
        $error = new Error($response);

        // Assert
        self::assertSame('Something went wrong', $error->getMessage());
    }

    public function testMessageWithErrorsIsConcatenated(): void
    {
        // Arrange
        $response = new ClientResponse([
            'message' => 'Validation failed',
            'errors'  => ['field' => 'is required', 'email' => 'invalid'],
        ], 422);

        // Action
        $error = new Error($response);

        // Assert
        self::assertStringContainsString('Validation failed', $error->getMessage());
        self::assertStringContainsString('is required', $error->getMessage());
        self::assertStringContainsString('invalid', $error->getMessage());
    }

    public function testMissingMessageKeyDegradesGracefully(): void
    {
        // Arrange
        $response = new ClientResponse([], 500);

        // Action
        $error = new Error($response);

        // Assert
        self::assertSame('', $error->getMessage());
    }

    public function testNonScalarMessageDegradesGracefully(): void
    {
        // Arrange
        $response = new ClientResponse(['message' => ['nested' => 'array']], 500);

        // Action
        $error = new Error($response);

        // Assert
        self::assertSame('', $error->getMessage());
    }

    public function testNonArrayErrorsKeyIsIgnored(): void
    {
        // Arrange
        $response = new ClientResponse(['message' => 'Oops', 'errors' => 'not-an-array'], 400);

        // Action
        $error = new Error($response);

        // Assert
        self::assertSame('Oops', $error->getMessage());
    }

    public function testOnlyErrorsWithoutMessageHasNoLeadingSpace(): void
    {
        // Arrange
        $response = new ClientResponse(['errors' => ['vin' => ['invalid']]], 422);

        // Action
        $error = new Error($response);

        // Assert
        self::assertSame('invalid', $error->getMessage());
    }

    public function testBoolsAndNullsInErrorsAreSkipped(): void
    {
        // Arrange
        $response = new ClientResponse([
            'errors' => ['active' => true, 'deleted' => false, 'key' => null, 'field' => 'required'],
        ], 400);

        // Action
        $error = new Error($response);

        // Assert
        self::assertSame('required', $error->getMessage());
    }

    public function testIntegerErrorValueIsIncluded(): void
    {
        // Arrange
        $response = new ClientResponse(['message' => 'Code', 'errors' => ['code' => 42]], 400);

        // Action
        $error = new Error($response);

        // Assert
        self::assertSame('Code 42', $error->getMessage());
    }

    public function testEmptyStringInErrorsIsSkipped(): void
    {
        // Arrange
        $response = new ClientResponse(['errors' => ['field' => '', 'other' => 'required']], 422);

        // Action
        $error = new Error($response);

        // Assert
        self::assertSame('required', $error->getMessage());
    }
}
