<?php

declare(strict_types=1);

namespace CheckVin\Api\Tests;

use CheckVin\Api\CheckVin;
use CheckVin\Api\Config\ApiUriGlossary;
use CheckVin\Api\Http\Response\ClientResponse;
use CheckVin\Api\Provider\Autocheck\AutocheckDataProviderInterface;
use CheckVin\Api\Provider\Balance\BalanceDataProviderInterface;
use CheckVin\Api\Provider\Carfax\CarfaxDataProviderInterface;
use CheckVin\Api\Provider\Vehicle\VehicleDataProviderInterface;
use CheckVin\Api\Tests\Doubles\StubClient;
use PHPUnit\Framework\TestCase;

final class CheckVinTest extends TestCase
{
    private const API_KEY = 'test-api-key';
    private const VIN_CODE = '1FM5K7D85HGB31870';

    public function testAutocheckReturnsCorrectInterface(): void
    {
        // Arrange
        $checkVin = new CheckVin(self::API_KEY, new StubClient());

        // Action
        $provider = $checkVin->autocheck();

        // Assert
        self::assertInstanceOf(AutocheckDataProviderInterface::class, $provider);
    }

    public function testCarfaxReturnsCorrectInterface(): void
    {
        // Arrange
        $checkVin = new CheckVin(self::API_KEY, new StubClient());

        // Action
        $provider = $checkVin->carfax();

        // Assert
        self::assertInstanceOf(CarfaxDataProviderInterface::class, $provider);
    }

    public function testBalanceReturnsCorrectInterface(): void
    {
        // Arrange
        $checkVin = new CheckVin(self::API_KEY, new StubClient());

        // Action
        $provider = $checkVin->balance();

        // Assert
        self::assertInstanceOf(BalanceDataProviderInterface::class, $provider);
    }

    public function testVehicleReturnsCorrectInterface(): void
    {
        // Arrange
        $checkVin = new CheckVin(self::API_KEY, new StubClient());

        // Action
        $provider = $checkVin->vehicle();

        // Assert
        self::assertInstanceOf(VehicleDataProviderInterface::class, $provider);
    }

    public function testAllProvidersShareTheSameInjectedClient(): void
    {
        // Arrange
        $stub = new StubClient();
        $stub->stubResponse(new ClientResponse(['balance' => 10], 200));
        $checkVin = new CheckVin(self::API_KEY, $stub);

        // Action
        $checkVin->balance()->getBalance();

        // Assert
        self::assertTrue($stub->wasRequestCalled());
        self::assertSame(self::API_KEY, $stub->getLastParams()['api_key']);
    }

    public function testAutocheckUsesInjectedClientWithApiKey(): void
    {
        // Arrange
        $stub = new StubClient();
        $stub->stubResponse(new ClientResponse(['has_autocheck' => true, 'autocheck_data' => ''], 200));
        $checkVin = new CheckVin(self::API_KEY, $stub);

        // Action
        $checkVin->autocheck()->getAutoCheckForVinCode(self::VIN_CODE);

        // Assert
        self::assertSame(ApiUriGlossary::VIN_AUTOCHECK_PATH, $stub->getLastPath());
        self::assertSame(self::API_KEY, $stub->getLastParams()['api_key']);
    }

    public function testCarfaxUsesInjectedClientWithApiKey(): void
    {
        // Arrange
        $stub = new StubClient();
        $stub->stubResponse(new ClientResponse(['has_carfax' => true, 'carfax_data' => ''], 200));
        $checkVin = new CheckVin(self::API_KEY, $stub);

        // Action
        $checkVin->carfax()->getCarfaxForVinCode(self::VIN_CODE);

        // Assert
        self::assertSame(ApiUriGlossary::VIN_CARFAX_PATH, $stub->getLastPath());
        self::assertSame(self::API_KEY, $stub->getLastParams()['api_key']);
    }

    public function testVehicleUsesInjectedClientWithApiKey(): void
    {
        // Arrange
        $stub = new StubClient();
        $stub->stubResponse(new ClientResponse(['make' => 'FORD'], 200));
        $checkVin = new CheckVin(self::API_KEY, $stub);

        // Action
        $checkVin->vehicle()->getInfo(self::VIN_CODE);

        // Assert
        self::assertSame(ApiUriGlossary::VEHICLE_INFO_PATH, $stub->getLastPath());
        self::assertSame(self::API_KEY, $stub->getLastParams()['api_key']);
    }

    public function testAutocheckReturnsSameInstanceOnRepeatedCalls(): void
    {
        // Arrange
        $checkVin = new CheckVin(self::API_KEY, new StubClient());

        // Action
        $first = $checkVin->autocheck();
        $second = $checkVin->autocheck();

        // Assert
        self::assertSame($first, $second);
    }

    public function testCarfaxReturnsSameInstanceOnRepeatedCalls(): void
    {
        // Arrange
        $checkVin = new CheckVin(self::API_KEY, new StubClient());

        // Action
        $first = $checkVin->carfax();
        $second = $checkVin->carfax();

        // Assert
        self::assertSame($first, $second);
    }

    public function testBalanceReturnsSameInstanceOnRepeatedCalls(): void
    {
        // Arrange
        $checkVin = new CheckVin(self::API_KEY, new StubClient());

        // Action
        $first = $checkVin->balance();
        $second = $checkVin->balance();

        // Assert
        self::assertSame($first, $second);
    }

    public function testVehicleReturnsSameInstanceOnRepeatedCalls(): void
    {
        // Arrange
        $checkVin = new CheckVin(self::API_KEY, new StubClient());

        // Action
        $first = $checkVin->vehicle();
        $second = $checkVin->vehicle();

        // Assert
        self::assertSame($first, $second);
    }
}
