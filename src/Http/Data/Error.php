<?php

declare(strict_types=1);

namespace CheckVin\Api\Http\Data;

use CheckVin\Api\Http\Response\ClientResponse;

final class Error
{
    private string $message;

    public function __construct(ClientResponse $clientResponse)
    {
        $this->message = $this->buildMessage($clientResponse->getData());
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    private function buildMessage(array $data): string
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
