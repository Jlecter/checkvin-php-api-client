<?php

declare(strict_types=1);

namespace CheckVin\Api\Http\Response;

final class ClientResponse
{
    public function __construct(
        private readonly array $data,
        private readonly int $httpCode,
    ) {
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getResponseHttpCode(): int
    {
        return $this->httpCode;
    }
}
