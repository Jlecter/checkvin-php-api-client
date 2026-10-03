<?php

declare(strict_types=1);

namespace CheckVin\Api\Tests\Doubles;

use CheckVin\Api\Http\Client\ClientInterface;
use CheckVin\Api\Http\Response\Abstraction\ApiResponse;
use CheckVin\Api\Http\Response\Abstraction\SuccessResponse;
use CheckVin\Api\Http\Response\ClientResponse;
use CheckVin\Api\Http\Response\Error\ApplicationErrorResponse;
use CheckVin\Api\Http\Response\Success\ApplicationSuccessResponse;

final class StubClient implements ClientInterface
{
    private string $lastPath = '';
    private array $lastParams = [];
    private ?ClientResponse $stubbedResponse = null;

    public function stubResponse(ClientResponse $response): void
    {
        $this->stubbedResponse = $response;
    }

    public function getLastPath(): string
    {
        return $this->lastPath;
    }

    public function getLastParams(): array
    {
        return $this->lastParams;
    }

    public function request(string $path, array $params): ClientResponse
    {
        $this->lastPath = $path;
        $this->lastParams = $params;

        return $this->stubbedResponse ?? new ClientResponse([], SuccessResponse::SUCCESS_CODE);
    }

    public function makeResponse(ClientResponse $clientResponse): ApiResponse
    {
        if ($clientResponse->getResponseHttpCode() !== SuccessResponse::SUCCESS_CODE) {
            return new ApplicationErrorResponse($clientResponse);
        }

        return new ApplicationSuccessResponse($clientResponse);
    }
}
