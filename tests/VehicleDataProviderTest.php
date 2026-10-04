<?php

declare(strict_types=1);

namespace CheckVin\Api\Tests;

use CheckVin\Api\Config\ApiUriGlossary;
use CheckVin\Api\Http\Response\ClientResponse;
use CheckVin\Api\Provider\Vehicle\VehicleDataProvider;
use CheckVin\Api\Tests\Doubles\StubClient;
use PHPUnit\Framework\TestCase;

final class VehicleDataProviderTest extends TestCase
{
    private const API_KEY = 'test-api-key';
    private const VIN_CODE = '1FM5K7D85HGB31870';

    public function testGetInfoSendsCorrectPathAndParams(): void
    {
        // Arrange
        $stub = new StubClient();
        $stub->stubResponse(new ClientResponse(['make' => 'Honda', 'model' => 'Civic'], 200));
        $provider = new VehicleDataProvider(self::API_KEY, $stub);

        // Action
        $provider->getInfo(self::VIN_CODE);

        // Assert
        self::assertSame(ApiUriGlossary::VEHICLE_INFO_PATH, $stub->getLastPath());
        self::assertSame(self::API_KEY, $stub->getLastParams()['api_key']);
        self::assertSame(self::VIN_CODE, $stub->getLastParams()['vincode']);
    }

    public function testGetInfoReturnsSuccessResponse(): void
    {
        // Arrange
        $stub = new StubClient();
        $stub->stubResponse(new ClientResponse(['make' => 'Honda', 'model' => 'Civic'], 200));
        $provider = new VehicleDataProvider(self::API_KEY, $stub);

        // Action
        $response = $provider->getInfo(self::VIN_CODE);

        // Assert
        self::assertTrue($response->isSuccess());
        self::assertSame('Honda', $response->getData()['make']);
    }

    public function testGetInfoReturnsErrorOnFailure(): void
    {
        // Arrange
        $stub = new StubClient();
        $stub->stubResponse(new ClientResponse(['message' => 'VIN not found'], 404));
        $provider = new VehicleDataProvider(self::API_KEY, $stub);

        // Action
        $response = $provider->getInfo(self::VIN_CODE);

        // Assert
        self::assertFalse($response->isSuccess());
        self::assertSame('VIN not found', $response->getError()->getMessage());
    }
}
