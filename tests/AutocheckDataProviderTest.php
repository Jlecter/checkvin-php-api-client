<?php

declare(strict_types=1);

namespace CheckVin\Api\Tests;

use CheckVin\Api\Config\ApiUriGlossary;
use CheckVin\Api\Http\Response\ClientResponse;
use CheckVin\Api\Provider\Autocheck\AutocheckDataProvider;
use CheckVin\Api\Tests\Doubles\StubClient;
use PHPUnit\Framework\TestCase;

final class AutocheckDataProviderTest extends TestCase
{
    private const API_KEY = 'test-api-key';
    private const VIN_CODE = '1FM5K7D85HGB31870';

    public function testGetAutoCheckSendsCorrectPathAndParams(): void
    {
        // Arrange
        $stub = new StubClient();
        $stub->stubResponse(new ClientResponse(['vin' => [], 'has_autocheck' => true], 200));
        $provider = new AutocheckDataProvider(self::API_KEY, $stub);

        // Action
        $provider->getAutoCheckForVinCode(self::VIN_CODE);

        // Assert
        self::assertSame(ApiUriGlossary::VIN_AUTOCHECK_PATH, $stub->getLastPath());
        self::assertSame(self::API_KEY, $stub->getLastParams()['api_key']);
        self::assertSame(self::VIN_CODE, $stub->getLastParams()['vincode']);
    }

    public function testGetAutoCheckReturnsSuccessResponse(): void
    {
        // Arrange
        $stub = new StubClient();
        $stub->stubResponse(new ClientResponse(['has_autocheck' => true, 'autocheck_data' => '<html>...'], 200));
        $provider = new AutocheckDataProvider(self::API_KEY, $stub);

        // Action
        $response = $provider->getAutoCheckForVinCode(self::VIN_CODE);

        // Assert
        self::assertTrue($response->isSuccess());
        self::assertArrayHasKey('has_autocheck', $response->getData());
    }

    public function testCheckReportExistsSendsCorrectPath(): void
    {
        // Arrange
        $stub = new StubClient();
        $stub->stubResponse(new ClientResponse(['message' => 'Report found', 'preset_link' => 'https://example.com/hash'], 200));
        $provider = new AutocheckDataProvider(self::API_KEY, $stub);

        // Action
        $provider->checkReportExists(self::VIN_CODE);

        // Assert
        self::assertSame(ApiUriGlossary::VIN_AUTOCHECK_REPORT_EXIST_PATH, $stub->getLastPath());
        self::assertSame(self::API_KEY, $stub->getLastParams()['api_key']);
        self::assertSame(self::VIN_CODE, $stub->getLastParams()['vincode']);
    }

    public function testCheckReportExistsReturnsTrueWhenFound(): void
    {
        // Arrange
        $stub = new StubClient();
        $stub->stubResponse(new ClientResponse(['message' => 'Report found', 'preset_link' => 'https://example.com/hash'], 200));
        $provider = new AutocheckDataProvider(self::API_KEY, $stub);

        // Action
        $response = $provider->checkReportExists(self::VIN_CODE);

        // Assert
        self::assertTrue($response->isSuccess());
        self::assertSame('Report found', $response->getData()['message']);
    }

    public function testCheckReportExistsReturnsErrorWhenNotFound(): void
    {
        // Arrange
        $stub = new StubClient();
        $stub->stubResponse(new ClientResponse(['message' => 'Report not found', 'preset_link' => ''], 404));
        $provider = new AutocheckDataProvider(self::API_KEY, $stub);

        // Action
        $response = $provider->checkReportExists(self::VIN_CODE);

        // Assert
        self::assertFalse($response->isSuccess());
        self::assertSame('Report not found', $response->getError()->getMessage());
    }
}
