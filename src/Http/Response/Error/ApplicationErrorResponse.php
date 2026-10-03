<?php

declare(strict_types=1);

namespace CheckVin\Api\Http\Response\Error;

use CheckVin\Api\Http\Data\Error;
use CheckVin\Api\Http\Response\Abstraction\ErrorResponse;
use CheckVin\Api\Http\Response\ClientResponse;

final class ApplicationErrorResponse extends ErrorResponse
{
    private readonly Error $error;

    public function __construct(ClientResponse $response)
    {
        $this->error = new Error($response);
    }

    public function getError(): Error
    {
        return $this->error;
    }
}
