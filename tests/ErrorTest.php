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
        $error = Error::fromClientResponse($response);

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
        $error = Error::fromClientResponse($response);

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
        $error = Error::fromClientResponse($response);

        // Assert
        self::assertSame('', $error->getMessage());
    }

    public function testArrayMessageIsFlattenedRecursively(): void
    {
        // Arrange
        $response = new ClientResponse(['message' => ['nested' => 'array']], 500);

        // Action
        $error = Error::fromClientResponse($response);

        // Assert
        self::assertSame('array', $error->getMessage());
    }

    public function testArrayMessageCombinedWithErrors(): void
    {
        // Arrange
        $response = new ClientResponse([
            'message' => ['title' => 'Validation failed'],
            'errors'  => ['email' => 'invalid'],
        ], 422);

        // Action
        $error = Error::fromClientResponse($response);

        // Assert
        self::assertSame('Validation failed invalid', $error->getMessage());
    }

    public function testScalarStringErrorsNormalisedToList(): void
    {
        // Arrange — API occasionally returns errors as a plain string instead of an object
        $response = new ClientResponse(['message' => 'Oops', 'errors' => 'not-an-array'], 400);

        // Action
        $error = Error::fromClientResponse($response);

        // Assert
        self::assertSame(['not-an-array'], $error->getErrors());
        self::assertSame('Oops not-an-array', $error->getMessage());
    }

    public function testOnlyErrorsWithoutMessageHasNoLeadingSpace(): void
    {
        // Arrange
        $response = new ClientResponse(['errors' => ['vin' => ['invalid']]], 422);

        // Action
        $error = Error::fromClientResponse($response);

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
        $error = Error::fromClientResponse($response);

        // Assert
        self::assertSame('required', $error->getMessage());
    }

    public function testIntegerErrorValueIsIncluded(): void
    {
        // Arrange
        $response = new ClientResponse(['message' => 'Code', 'errors' => ['code' => 42]], 400);

        // Action
        $error = Error::fromClientResponse($response);

        // Assert
        self::assertSame('Code 42', $error->getMessage());
    }

    public function testEmptyStringInErrorsIsSkipped(): void
    {
        // Arrange
        $response = new ClientResponse(['errors' => ['field' => '', 'other' => 'required']], 422);

        // Action
        $error = Error::fromClientResponse($response);

        // Assert
        self::assertSame('required', $error->getMessage());
    }

    public function testGetHttpCodeReturnsCodeFromClientResponse(): void
    {
        // Arrange
        $response = new ClientResponse(['message' => 'Unauthorized'], 401);

        // Action
        $error = Error::fromClientResponse($response);

        // Assert
        self::assertSame(401, $error->getHttpCode());
    }

    public function testGetErrorsReturnsRawErrorsArray(): void
    {
        // Arrange
        $errorsPayload = ['field' => 'is required', 'email' => 'invalid'];
        $response      = new ClientResponse(['message' => 'Validation failed', 'errors' => $errorsPayload], 422);

        // Action
        $error = Error::fromClientResponse($response);

        // Assert
        self::assertSame($errorsPayload, $error->getErrors());
    }

    public function testGetErrorsReturnsEmptyArrayWhenNoErrorsKey(): void
    {
        // Arrange
        $response = new ClientResponse(['message' => 'Not found'], 404);

        // Action
        $error = Error::fromClientResponse($response);

        // Assert
        self::assertSame([], $error->getErrors());
    }

    public function testGetErrorsReturnsEmptyArrayWhenErrorsIsNonPrintableScalar(): void
    {
        // Arrange — bools and null are not printable scalars; errors must be []
        $response = new ClientResponse(['message' => 'Oops', 'errors' => true], 400);

        // Action
        $error = Error::fromClientResponse($response);

        // Assert
        self::assertSame([], $error->getErrors());
    }

    public function testIsMalformedBodyReturnsTrueForInvalidBody(): void
    {
        // Arrange
        $response = ClientResponse::fromBody('not json', 502);

        // Action
        $error = Error::fromClientResponse($response);

        // Assert
        self::assertTrue($error->isMalformedBody());
    }

    public function testIsMalformedBodyReturnsFalseForValidBody(): void
    {
        // Arrange
        $response = new ClientResponse(['message' => 'Unauthorized'], 401);

        // Action
        $error = Error::fromClientResponse($response);

        // Assert
        self::assertFalse($error->isMalformedBody());
    }
}
