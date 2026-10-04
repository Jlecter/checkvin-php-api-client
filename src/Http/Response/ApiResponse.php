<?php

declare(strict_types=1);

namespace CheckVin\Api\Http\Response;

use CheckVin\Api\Http\Data\Error;

final class ApiResponse
{
    private const SUCCESS_CODE = 200;

    private function __construct(
        private readonly bool $success,
        private readonly ?Error $error,
        private readonly array $data,
    ) {
    }

    public static function fromClientResponse(ClientResponse $clientResponse): self
    {
        if ($clientResponse->getResponseHttpCode() !== self::SUCCESS_CODE || !$clientResponse->hasValidBody()) {
            return new self(false, Error::fromClientResponse($clientResponse), []);
        }

        return new self(true, null, $clientResponse->getData());
    }

    public function isSuccess(): bool
    {
        return $this->success;
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
