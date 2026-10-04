<?php

declare(strict_types=1);

namespace CheckVin\Api\Tests;

use CheckVin\Api\Exception\InvalidVinCode;
use CheckVin\Api\Provider\Autocheck\AutocheckDataProvider;
use CheckVin\Api\Tests\Doubles\StubClient;
use CheckvinVincode\Exception\CheckSumNotValidException;
use CheckvinVincode\Exception\LengthNotValidException;
use CheckvinVincode\Exception\NotValidLetterException;
use PHPUnit\Framework\TestCase;

final class VinValidationTest extends TestCase
{
    private const API_KEY = 'test-api-key';

    public function testNormalizedVinIsSentInRequest(): void
    {
        // Arrange — lowercase, dashes and spaces stripped by the package
        $stub = new StubClient();
        $provider = new AutocheckDataProvider(self::API_KEY, $stub);

        // Action
        $provider->getAutoCheckForVinCode('1fm5-k7d 85hgb31870');

        // Assert
        self::assertSame('1FM5K7D85HGB31870', $stub->getLastParams()['vincode']);
    }

    public function testTooShortVinThrowsInvalidVinCodeWithNoRequest(): void
    {
        // Arrange
        $stub = new StubClient();
        $provider = new AutocheckDataProvider(self::API_KEY, $stub);

        // Action
        try {
            $provider->getAutoCheckForVinCode('1FM5K7D85HGB');
            self::fail('Expected InvalidVinCode to be thrown');
        } catch (InvalidVinCode $e) {
            // Assert
            self::assertInstanceOf(LengthNotValidException::class, $e->getPrevious());
            self::assertFalse($stub->wasRequestCalled());
        }
    }

    public function testInvalidLetterThrowsInvalidVinCodeWithNoRequest(): void
    {
        // Arrange — 'O' at position 5 is not allowed
        $stub = new StubClient();
        $provider = new AutocheckDataProvider(self::API_KEY, $stub);

        // Action
        try {
            $provider->getAutoCheckForVinCode('1FM5O7D85HGB31870');
            self::fail('Expected InvalidVinCode to be thrown');
        } catch (InvalidVinCode $e) {
            // Assert
            self::assertInstanceOf(NotValidLetterException::class, $e->getPrevious());
            self::assertFalse($stub->wasRequestCalled());
        }
    }

    public function testBadChecksumThrowsInvalidVinCodeWithNoRequest(): void
    {
        // Arrange — last digit changed so check digit at position 9 is wrong
        $stub = new StubClient();
        $provider = new AutocheckDataProvider(self::API_KEY, $stub);

        // Action
        try {
            $provider->getAutoCheckForVinCode('1FM5K7D85HGB31871');
            self::fail('Expected InvalidVinCode to be thrown');
        } catch (InvalidVinCode $e) {
            // Assert
            self::assertInstanceOf(CheckSumNotValidException::class, $e->getPrevious());
            self::assertFalse($stub->wasRequestCalled());
        }
    }

    public function testInvalidVinCodeImplementsCheckVinApiException(): void
    {
        // Arrange
        $stub = new StubClient();
        $provider = new AutocheckDataProvider(self::API_KEY, $stub);

        // Action
        try {
            $provider->getAutoCheckForVinCode('TOOSHORT');
            self::fail('Expected InvalidVinCode to be thrown');
        } catch (\CheckVin\Api\Exception\CheckVinApiException $e) {
            // Assert — InvalidVinCode is catchable via the SDK exception interface
            self::assertInstanceOf(InvalidVinCode::class, $e);
        }
    }
}
