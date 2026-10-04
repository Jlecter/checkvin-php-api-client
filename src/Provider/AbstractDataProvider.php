<?php

declare(strict_types=1);

namespace CheckVin\Api\Provider;

use CheckVin\Api\Config\Config;
use CheckVin\Api\Exception\InvalidVinCode;
use CheckVin\Api\Http\Client\Client;
use CheckVin\Api\Http\Client\ClientInterface;
use CheckVin\Api\Http\Response\ApiResponse;
use CheckvinVincode\VinCode;

abstract class AbstractDataProvider
{
    private const QUERY_PARAM_API_KEY = 'api_key';
    private const QUERY_PARAM_VIN_CODE = 'vincode';

    private readonly ClientInterface $client;

    public function __construct(#[\SensitiveParameter] private readonly string $apiKey, ?ClientInterface $client = null)
    {
        $this->client = $client ?? new Client(new Config());
    }

    /**
     * @throws InvalidVinCode
     */
    protected function callForVin(string $path, string $vinCode): ApiResponse
    {
        return $this->call($path, [self::QUERY_PARAM_VIN_CODE => $this->vinCode($vinCode)]);
    }

    private function vinCode(string $vinCode): string
    {
        try {
            return (string) VinCode::createFromString($vinCode);
        } catch (\InvalidArgumentException $e) {
            throw new InvalidVinCode($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function call(string $path, array $params = []): ApiResponse
    {
        return ApiResponse::fromClientResponse(
            $this->client->request($path, [self::QUERY_PARAM_API_KEY => $this->apiKey] + $params),
        );
    }
}
