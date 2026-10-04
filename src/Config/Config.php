<?php

declare(strict_types=1);

namespace CheckVin\Api\Config;

use CheckVin\Api\Exception\InvalidConfig;

final class Config
{
    public const DEFAULT_HOST = 'https://apicheckvin.xyz';

    private readonly string $host;
    private readonly int $connectTimeoutMs;
    private readonly int $timeoutMs;

    public function __construct(
        string $host = self::DEFAULT_HOST,
        int $connectTimeoutMs = 10000,
        int $timeoutMs = 60000,
    ) {
        if ($connectTimeoutMs <= 0) {
            throw new InvalidConfig('connectTimeoutMs must be greater than 0');
        }

        if ($timeoutMs <= 0) {
            throw new InvalidConfig('timeoutMs must be greater than 0');
        }

        $this->host = rtrim($host, '/');
        $this->connectTimeoutMs = $connectTimeoutMs;
        $this->timeoutMs = $timeoutMs;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getConnectTimeoutMs(): int
    {
        return $this->connectTimeoutMs;
    }

    public function getTimeoutMs(): int
    {
        return $this->timeoutMs;
    }
}
