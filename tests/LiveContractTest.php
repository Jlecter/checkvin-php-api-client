<?php

declare(strict_types=1);

namespace CheckVin\Api\Tests;

use CheckVin\Api\Http\Response\ApiResponse;
use CheckVin\Api\Http\Response\ClientResponse;
use PHPUnit\Framework\TestCase;

/**
 * Contract tests against real API responses captured manually.
 * No network calls are made — fixtures are loaded from tests/Fixtures/live/.
 */
final class LiveContractTest extends TestCase
{
    // -------------------------------------------------------------------------
    // 200 success fixtures
    // -------------------------------------------------------------------------

    public function testBalanceReturnsFloatMessage(): void
    {
        // Arrange / Action
        $response = $this->parseFixture('balance_200.json', 200);

        // Assert
        self::assertTrue($response->isSuccess());
        self::assertSame(12.6, $response->getData()['message']);
    }

    public function testCarfaxCheckFoundIsSuccess(): void
    {
        // Arrange / Action
        $response = $this->parseFixture('carfax_check_found_200.json', 200);

        // Assert
        self::assertTrue($response->isSuccess());
        $data = $response->getData();
        self::assertSame('Report found', $data['message']);
        self::assertTrue($data['checked']);
        self::assertIsString($data['preset_link']);
    }

    public function testAutocheckCheckFoundIsSuccess(): void
    {
        // Arrange / Action
        $response = $this->parseFixture('autocheck_check_found_200.json', 200);

        // Assert
        self::assertTrue($response->isSuccess());
        $data = $response->getData();
        self::assertSame('Report found', $data['message']);
        self::assertIsString($data['preset_link']);
    }

    public function testAutocheckReportIsSuccess(): void
    {
        // Arrange / Action
        $response = $this->parseFixture('autocheck_report_200.json', 200);

        // Assert
        self::assertTrue($response->isSuccess());
        $data = $response->getData();
        self::assertTrue($data['has_autocheck']);
        // The server returns "vin":{} — an empty object — instead of a VIN string; server quirk, not a library bug.
        self::assertSame([], $data['vin']);
        self::assertIsString($data['updated_at']);
        self::assertIsString($data['autocheck_data']);
        self::assertIsString($data['link']);
    }

    public function testCarfaxReportIsSuccess(): void
    {
        // Arrange / Action
        $response = $this->parseFixture('carfax_report_200.json', 200);

        // Assert
        self::assertTrue($response->isSuccess());
        $data = $response->getData();
        self::assertTrue($data['has_carfax']);
        self::assertIsString($data['vin']);
        self::assertIsString($data['hash']);
        self::assertIsString($data['updated_at']);
        self::assertIsString($data['carfax_data']);
        self::assertIsString($data['link']);
    }

    public function testVehicleInfoIsSuccess(): void
    {
        // Arrange / Action
        $response = $this->parseFixture('vehicle_info_200.json', 200);

        // Assert
        self::assertTrue($response->isSuccess());
        $data = $response->getData()['data'];
        self::assertSame('Volkswagen', $data['brand']);
        self::assertSame('Tiguan', $data['model']);
        self::assertIsString($data['vin']);
        self::assertIsInt($data['year']);
        self::assertSame(2012, $data['year']);
        self::assertIsString($data['engine_fuel']);
        self::assertIsInt($data['engine_hp']);
    }

    // -------------------------------------------------------------------------
    // 422 error fixtures
    // -------------------------------------------------------------------------

    public function testInvalidApiKeyReturns422(): void
    {
        // Arrange / Action
        $response = $this->parseFixture('invalid_api_key_422.json', 422);

        // Assert
        self::assertFalse($response->isSuccess());
        $error = $response->getError();
        self::assertSame(422, $error->getHttpCode());
        self::assertSame('Пользователь не найден', $error->getMessage());
        self::assertFalse($error->isMalformedBody());
    }

    public function testValidation422FlattenedMessage(): void
    {
        // Arrange / Action
        $response = $this->parseFixture('validation_422.json', 422);

        // Assert
        self::assertFalse($response->isSuccess());
        $error = $response->getError();
        self::assertSame(422, $error->getHttpCode());
        // getMessage() concatenates the top-level message with flattened errors
        self::assertSame(
            'The given data was invalid. The vincode must be at least 17 characters.',
            $error->getMessage(),
        );
        self::assertSame(
            ['vincode' => ['The vincode must be at least 17 characters.']],
            $error->getErrors(),
        );
        self::assertFalse($error->isMalformedBody());
    }

    public function testVinChecksumFailure422(): void
    {
        // Arrange / Action
        $response = $this->parseFixture('vin_checksum_422.json', 422);

        // Assert
        self::assertFalse($response->isSuccess());
        $error = $response->getError();
        self::assertSame(422, $error->getHttpCode());
        self::assertSame(
            'Vincode Invalid. Checksum failed verification, check vincode please!',
            $error->getMessage(),
        );
        self::assertFalse($error->isMalformedBody());
    }

    public function testVinNotNorthAmerica422(): void
    {
        // Arrange / Action
        $response = $this->parseFixture('vin_not_north_america_422.json', 422);

        // Assert
        self::assertFalse($response->isSuccess());
        $error = $response->getError();
        self::assertSame(422, $error->getHttpCode());
        self::assertSame('Vincode Invalid. Auto not from North America.', $error->getMessage());
        self::assertFalse($error->isMalformedBody());
    }

    // -------------------------------------------------------------------------
    // 404 check-not-found fixtures
    // -------------------------------------------------------------------------

    public function testCarfaxCheckNotFoundBodyAvailableViaError(): void
    {
        // Arrange / Action
        $response = $this->parseFixture('carfax_check_not_found_404.json', 404);

        // Assert
        self::assertFalse($response->isSuccess());
        $error = $response->getError();
        self::assertSame('Report not found', $error->getMessage());
        $body = $error->getData();
        // checked is true even when the report is not found — it means the lookup ran, not that a report exists
        self::assertTrue($body['checked']);
        self::assertSame('', $body['preset_link']);
    }

    public function testAutocheckCheckNotFoundBodyAvailableViaError(): void
    {
        // Arrange / Action
        $response = $this->parseFixture('autocheck_check_not_found_404.json', 404);

        // Assert
        self::assertFalse($response->isSuccess());
        $error = $response->getError();
        self::assertSame('Report not found', $error->getMessage());
        $body = $error->getData();
        self::assertSame('', $body['preset_link']);
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    private function parseFixture(string $filename, int $httpStatus): ApiResponse
    {
        $path = __DIR__ . '/Fixtures/live/' . $filename;
        $body = file_get_contents($path);

        return ApiResponse::fromClientResponse(
            ClientResponse::fromBody($body, $httpStatus),
        );
    }
}
