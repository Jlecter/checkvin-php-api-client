<?php

declare(strict_types=1);

namespace CheckVin\Api\Provider;

use CheckVin\Api\Config\Config;
use CheckVin\Api\Http\Client\Client;
use CheckVin\Api\Http\Client\ClientInterface;
use CheckVin\Api\Http\Response\Abstraction\ApiResponse;
use CheckVin\Api\Http\Response\ApiResponseFactory;

abstract class AbstractDataProvider
{
    private const QUERY_PARAM_API_KEY = 'api_key';
    protected const QUERY_PARAM_VIN_CODE = 'vincode';

    private readonly ClientInterface $client;

    public function __construct(private readonly string $apiKey, ?ClientInterface $client = null)
    {
        $this->client = $client ?? new Client(new Config());
    }

    protected function call(string $path, array $params = []): ApiResponse
    {
        return ApiResponseFactory::fromClientResponse(
            $this->client->request($path, [self::QUERY_PARAM_API_KEY => $this->apiKey] + $params),
        );
    }
}
