<?php

declare(strict_types=1);

namespace CheckVin\Api\Http\Response;

use CheckVin\Api\Http\Data\Error;

final class ApiResponse
{
    private const SUCCESS_CODE = 200;

    private function __construct(
        private readonly ?Error $error,
        private readonly array $data,
    ) {
    }

    public static function fromClientResponse(ClientResponse $clientResponse): self
    {
        if ($clientResponse->getResponseHttpCode() !== self::SUCCESS_CODE || !$clientResponse->hasValidBody()) {
            return new self(Error::fromClientResponse($clientResponse), []);
        }

        return new self(null, $clientResponse->getData());
    }

    public function isSuccess(): bool
    {
        return $this->error === null;
    }

    public function getError(): ?Error
    {
        return $this->error;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
