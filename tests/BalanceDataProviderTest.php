<?php

declare(strict_types=1);

namespace CheckVin\Api\Tests;

use CheckVin\Api\Config\ApiUriGlossary;
use CheckVin\Api\Http\Response\ClientResponse;
use CheckVin\Api\Provider\Balance\BalanceDataProvider;
use CheckVin\Api\Tests\Doubles\StubClient;
use PHPUnit\Framework\TestCase;

final class BalanceDataProviderTest extends TestCase
{
    private const API_KEY = 'test-api-key';

    public function testGetBalanceSendsCorrectPathAndApiKey(): void
    {
        // Arrange
        $stub = new StubClient();
        $stub->stubResponse(new ClientResponse(['balance' => 10], 200));
        $provider = new BalanceDataProvider(self::API_KEY, $stub);

        // Action
        $provider->getBalance();

        // Assert
        self::assertSame(ApiUriGlossary::CHECK_BALANCE_PATH, $stub->getLastPath());
        self::assertSame(self::API_KEY, $stub->getLastParams()['api_key']);
        self::assertArrayNotHasKey('vincode', $stub->getLastParams());
    }

    public function testGetBalanceReturnsSuccessResponse(): void
    {
        // Arrange
        $stub = new StubClient();
        $stub->stubResponse(new ClientResponse(['balance' => 42], 200));
        $provider = new BalanceDataProvider(self::API_KEY, $stub);

        // Action
        $response = $provider->getBalance();

        // Assert
        self::assertTrue($response->isSuccess());
        self::assertSame(42, $response->getData()['balance']);
    }

    public function testGetBalanceReturnsErrorOnFailure(): void
    {
        // Arrange
        $stub = new StubClient();
        $stub->stubResponse(new ClientResponse(['message' => 'Unauthorized'], 401));
        $provider = new BalanceDataProvider(self::API_KEY, $stub);

        // Action
        $response = $provider->getBalance();

        // Assert
        self::assertFalse($response->isSuccess());
        self::assertSame('Unauthorized', $response->getError()->getMessage());
    }
}
