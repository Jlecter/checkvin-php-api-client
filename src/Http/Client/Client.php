<?php

declare(strict_types=1);

namespace CheckVin\Api\Http\Client;

use CheckVin\Api\Config\Config;
use CheckVin\Api\Exception\RequestFailed;
use CheckVin\Api\Http\Response\Abstraction\ApiResponse;
use CheckVin\Api\Http\Response\ClientResponse;
use CheckVin\Api\Http\Response\Error\ApplicationErrorResponse;
use CheckVin\Api\Http\Response\Success\ApplicationSuccessResponse;

final class Client implements ClientInterface
{
    public function __construct(private readonly Config $config)
    {
    }

    public function request(string $path, array $params): ClientResponse
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $this->buildRequestUrl($path, $params));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, $this->config->getConnectTimeoutMs());
        curl_setopt($curl, CURLOPT_TIMEOUT_MS, $this->config->getTimeoutMs());

        $output = curl_exec($curl);

        if ($output === false) {
            $errno = curl_errno($curl);
            $error = curl_error($curl);
            // CurlHandle freed when it goes out of scope; curl_close() deprecated since PHP 8.5
            throw new RequestFailed(
                sprintf('Request failed (errno %d): %s', $errno, $error),
                $errno,
            );
        }

        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);

        return ClientResponse::fromBody($output, $httpCode);
    }

    public function makeResponse(ClientResponse $clientResponse): ApiResponse
    {
        if ($clientResponse->getResponseHttpCode() !== ApplicationSuccessResponse::SUCCESS_CODE) {
            return new ApplicationErrorResponse($clientResponse);
        }

        return new ApplicationSuccessResponse($clientResponse);
    }

    private function buildRequestUrl(string $path, array $params): string
    {
        return $this->config->getHost() . $path . '?' . http_build_query($params);
    }
}
