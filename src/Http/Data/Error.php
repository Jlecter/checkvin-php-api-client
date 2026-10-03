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
        $raw = $data['message'] ?? '';
        $message = is_scalar($raw) ? (string) $raw : '';

        if (is_array($data['errors'] ?? null)) {
            array_walk_recursive($data['errors'], function (mixed $value) use (&$message): void {
                if (is_scalar($value)) {
                    $message .= ' ' . $value;
                }
            });
        }

        return $message;
    }
}
