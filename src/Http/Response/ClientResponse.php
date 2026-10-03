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

    public static function fromBody(string $body, int $httpCode): self
    {
        $decoded = json_decode($body, true);

        if (!is_array($decoded)) {
            return new self(['message' => 'Malformed response body'], 0);
        }

        return new self($decoded, $httpCode);
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
