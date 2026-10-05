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
        $errors = self::normalizeErrors($data['errors'] ?? null);

        return new self(
            self::buildMessage($data['message'] ?? null, $errors),
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

    private static function normalizeErrors(mixed $raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }

        if (self::isPrintableScalar($raw)) {
            return [$raw];
        }

        return [];
    }

    private static function buildMessage(mixed $message, array $errors): string
    {
        $parts = [];

        if (self::isPrintableScalar($message)) {
            $parts[] = (string) $message;
        } elseif (is_array($message)) {
            array_walk_recursive($message, function (mixed $value) use (&$parts): void {
                if (self::isPrintableScalar($value)) {
                    $parts[] = (string) $value;
                }
            });
        }

        array_walk_recursive($errors, function (mixed $value) use (&$parts): void {
            if (self::isPrintableScalar($value)) {
                $parts[] = (string) $value;
            }
        });

        return implode(' ', $parts);
    }

    private static function isPrintableScalar(mixed $value): bool
    {
        return (is_string($value) && $value !== '') || is_int($value) || is_float($value);
    }
}
