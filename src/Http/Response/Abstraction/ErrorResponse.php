<?php

declare(strict_types=1);

namespace CheckVin\Api\Http\Response\Abstraction;

abstract class ErrorResponse extends ApiResponse
{
    public function isSuccess(): bool
    {
        return false;
    }

    public function getData(): array
    {
        return [];
    }
}
