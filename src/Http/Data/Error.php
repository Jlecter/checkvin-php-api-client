<?php

declare(strict_types=1);

namespace CheckVin\Api\Http\Data;

use CheckVin\Api\Http\Response\ClientResponse;

final class Error
{
    private function __construct(
        private readonly string $message,
        private readonly int $httpCode,
        private readonly array $errors,
        private readonly bool $malformedBody,
    ) {
    }

    public static function fromClientResponse(ClientResponse $clientResponse): self
    {
        $data = $clientResponse->getData();
        $errors = is_array($data['errors'] ?? null) ? $data['errors'] : [];

        return new self(
            self::buildMessage($data),
            $clientResponse->getResponseHttpCode(),
            $errors,
            !$clientResponse->hasValidBody(),
        );
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function isMalformedBody(): bool
    {
        return $this->malformedBody;
    }

    private static function buildMessage(array $data): string
    {
        $parts = [];

        $raw = $data['message'] ?? null;

        if ((is_string($raw) && $raw !== '') || is_int($raw) || is_float($raw)) {
            $parts[] = (string) $raw;
        }

        if (is_array($data['errors'] ?? null)) {
            array_walk_recursive($data['errors'], function (mixed $value) use (&$parts): void {
                if ((is_string($value) && $value !== '') || is_int($value) || is_float($value)) {
                    $parts[] = (string) $value;
                }
            });
        }

        return implode(' ', $parts);
    }
}
