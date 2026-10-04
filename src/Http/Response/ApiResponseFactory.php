<?php

declare(strict_types=1);

namespace CheckVin\Api\Http\Response;

use CheckVin\Api\Http\Response\Abstraction\ApiResponse;
use CheckVin\Api\Http\Response\Abstraction\SuccessResponse;
use CheckVin\Api\Http\Response\Error\ApplicationErrorResponse;
use CheckVin\Api\Http\Response\Success\ApplicationSuccessResponse;

/**
 * Kept separate from ApiResponse so the abstract class does not depend on its own concrete subclasses.
 */
final class ApiResponseFactory
{
    public static function fromClientResponse(ClientResponse $clientResponse): ApiResponse
    {
        if ($clientResponse->getResponseHttpCode() !== SuccessResponse::SUCCESS_CODE || !$clientResponse->hasValidBody()) {
            return new ApplicationErrorResponse($clientResponse);
        }

        return new ApplicationSuccessResponse($clientResponse);
    }
}
