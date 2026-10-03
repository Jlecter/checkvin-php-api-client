<?php

declare(strict_types=1);

namespace CheckVin\Api\Http\Response\Success;

use CheckVin\Api\Http\Response\Abstraction\SuccessResponse;
use CheckVin\Api\Http\Response\ClientResponse;

final class ApplicationSuccessResponse extends SuccessResponse
{
    public function __construct(private readonly ClientResponse $response)
    {
    }

    public function getData(): array
    {
        return $this->response->getData();
    }
}
