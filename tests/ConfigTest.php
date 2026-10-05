<?php

declare(strict_types=1);

namespace CheckVin\Api\Tests;

use CheckVin\Api\Config\Config;
use CheckVin\Api\Exception\InvalidConfig;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function testDefaultConnectTimeoutIstenSeconds(): void
    {
        // Action
        $config = new Config();

        // Assert
        self::assertSame(10000, $config->getConnectTimeoutMs());
    }

    public function testDefaultTimeoutIsSixtySeconds(): void
    {
        // Action
        $config = new Config();

        // Assert
        self::assertSame(60000, $config->getTimeoutMs());
    }

    public function testHostTrailingSlashIsTrimmed(): void
    {
        // Arrange
        $config = new Config('https://apicheckvin.xyz/');

        // Assert
        self::assertSame('https://apicheckvin.xyz', $config->getHost());
    }

    public function testHostWithMultipleTrailingSlashesIsTrimmed(): void
    {
        // Arrange
        $config = new Config('https://apicheckvin.xyz///');

        // Assert
        self::assertSame('https://apicheckvin.xyz', $config->getHost());
    }

    public function testHostWithoutTrailingSlashIsUnchanged(): void
    {
        // Arrange
        $config = new Config('https://apicheckvin.xyz');

        // Assert
        self::assertSame('https://apicheckvin.xyz', $config->getHost());
    }

    public function testZeroConnectTimeoutThrowsInvalidConfig(): void
    {
        // Assert
        $this->expectException(InvalidConfig::class);

        // Action
        new Config(connectTimeoutMs: 0);
    }

    public function testNegativeConnectTimeoutThrowsInvalidConfig(): void
    {
        // Assert
        $this->expectException(InvalidConfig::class);

        // Action
        new Config(connectTimeoutMs: -1);
    }

    public function testZeroTimeoutThrowsInvalidConfig(): void
    {
        // Assert
        $this->expectException(InvalidConfig::class);

        // Action
        new Config(timeoutMs: 0);
    }

    public function testNegativeTimeoutThrowsInvalidConfig(): void
    {
        // Assert
        $this->expectException(InvalidConfig::class);

        // Action
        new Config(timeoutMs: -100);
    }

    public function testInvalidConfigIsInvalidArgumentException(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);

        // Action
        new Config(connectTimeoutMs: 0);
    }

    public function testTimeoutSmallerThanConnectTimeoutThrowsInvalidConfig(): void
    {
        // Assert
        $this->expectException(InvalidConfig::class);

        // Action — total timeout shorter than connect timeout makes connect timeout meaningless
        new Config(connectTimeoutMs: 5000, timeoutMs: 300);
    }

    public function testEqualTimeoutsAreAllowed(): void
    {
        // Arrange + Action — equal values are explicitly permitted
        $config = new Config(connectTimeoutMs: 1000, timeoutMs: 1000);

        // Assert
        self::assertSame(1000, $config->getConnectTimeoutMs());
        self::assertSame(1000, $config->getTimeoutMs());
    }

    public function testTimeoutGreaterThanConnectTimeoutIsAllowed(): void
    {
        // Arrange + Action
        $config = new Config(connectTimeoutMs: 1000, timeoutMs: 5000);

        // Assert
        self::assertSame(1000, $config->getConnectTimeoutMs());
        self::assertSame(5000, $config->getTimeoutMs());
    }
}
