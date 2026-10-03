<?php

declare(strict_types=1);

namespace CheckVin\Api\Http\Response\Abstraction;

use CheckVin\Api\Http\Data\Error;

abstract class ApiResponse
{
    abstract public function isSuccess(): bool;

    abstract public function getError(): ?Error;

    abstract public function getData(): array;
}
