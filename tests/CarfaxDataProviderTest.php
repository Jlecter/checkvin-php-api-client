<?php

declare(strict_types=1);

namespace CheckVin\Api\Tests;

use CheckVin\Api\Config\ApiUriGlossary;
use CheckVin\Api\Http\Response\ClientResponse;
use CheckVin\Api\Provider\Carfax\CarfaxDataProvider;
use CheckVin\Api\Tests\Doubles\StubClient;
use PHPUnit\Framework\TestCase;

final class CarfaxDataProviderTest extends TestCase
{
    private const API_KEY = 'test-api-key';
    private const VIN_CODE = '1FM5K7D85HGB31870';

    public function testGetCarfaxSendsCorrectPathAndParams(): void
    {
        // Arrange
        $stub = new StubClient();
        $stub->stubResponse(new ClientResponse(['carfax_data' => '<html>...'], 200));
        $provider = new CarfaxDataProvider(self::API_KEY, $stub);

        // Action
        $provider->getCarfaxForVinCode(self::VIN_CODE);

        // Assert
        self::assertSame(ApiUriGlossary::VIN_CARFAX_PATH, $stub->getLastPath());
        self::assertSame(self::API_KEY, $stub->getLastParams()['api_key']);
        self::assertSame(self::VIN_CODE, $stub->getLastParams()['vincode']);
    }

    public function testGetCarfaxReturnsSuccessResponse(): void
    {
        // Arrange
        $stub = new StubClient();
        $stub->stubResponse(new ClientResponse(['carfax_data' => '<html>...'], 200));
        $provider = new CarfaxDataProvider(self::API_KEY, $stub);

        // Action
        $response = $provider->getCarfaxForVinCode(self::VIN_CODE);

        // Assert
        self::assertTrue($response->isSuccess());
    }

    public function testCheckReportExistsSendsCorrectPath(): void
    {
        // Arrange
        $stub = new StubClient();
        $stub->stubResponse(new ClientResponse(['message' => 'Report found', 'preset_link' => 'https://example.com/hash'], 200));
        $provider = new CarfaxDataProvider(self::API_KEY, $stub);

        // Action
        $provider->checkReportExists(self::VIN_CODE);

        // Assert
        self::assertSame(ApiUriGlossary::VIN_CARFAX_REPORT_EXIST_PATH, $stub->getLastPath());
        self::assertSame(self::API_KEY, $stub->getLastParams()['api_key']);
        self::assertSame(self::VIN_CODE, $stub->getLastParams()['vincode']);
    }

    public function testCheckReportExistsReturnsErrorWhenNotFound(): void
    {
        // Arrange
        $stub = new StubClient();
        $stub->stubResponse(new ClientResponse(['message' => 'Report not found', 'preset_link' => ''], 404));
        $provider = new CarfaxDataProvider(self::API_KEY, $stub);

        // Action
        $response = $provider->checkReportExists(self::VIN_CODE);

        // Assert
        self::assertFalse($response->isSuccess());
        self::assertSame('Report not found', $response->getError()->getMessage());
    }
}
