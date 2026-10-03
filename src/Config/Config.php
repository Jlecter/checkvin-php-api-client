<?php

declare(strict_types=1);

namespace CheckVin\Api\Config;

final class Config
{
    public function __construct(
        private readonly string $host = 'https://apicheckvin.xyz',
        private readonly int $connectTimeoutMs = 2000,
        private readonly int $timeoutMs = 10000,
    ) {
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
