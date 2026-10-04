<?php

declare(strict_types=1);

namespace CheckVin\Api\Http\Response;

final class ClientResponse
{
    public function __construct(
        private readonly array $data,
        private readonly int $httpCode,
        private readonly bool $validBody = true,
    ) {
    }

    public static function fromBody(string $body, int $httpCode): self
    {
        $decoded = json_decode($body, true);

        // The API returns JSON objects; `{}` decodes to [] which is also a list, so keep it valid.
        $isJsonList = is_array($decoded) && count($decoded) > 0 && array_is_list($decoded);

        if (!is_array($decoded) || $isJsonList) {
            return new self(
                ['message' => sprintf('Malformed response body (HTTP %d)', $httpCode)],
                $httpCode,
                false,
            );
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

    public function hasValidBody(): bool
    {
        return $this->validBody;
    }
}
