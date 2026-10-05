<?php

declare(strict_types=1);

namespace CheckVin\Api\Http\Client;

use CheckVin\Api\Config\Config;
use CheckVin\Api\Exception\RequestFailed;
use CheckVin\Api\Http\Response\ClientResponse;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface as PsrClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

/**
 * PSR-18 adapter for users who supply their own HTTP client (Guzzle, Symfony HttpClient, etc.).
 *
 * The default host is taken from Config::DEFAULT_HOST so that it stays in one
 * place. Curl timeouts from Config are intentionally not used here — timeouts
 * must be configured directly on the PSR-18 client being passed in.
 */
final class Psr18Client implements ClientInterface
{
    private readonly string $host;

    public function __construct(
        private readonly PsrClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        string $host = Config::DEFAULT_HOST,
    ) {
        $this->host = rtrim($host, '/');
    }

    public function request(string $path, #[\SensitiveParameter] array $params): ClientResponse
    {
        $url = RequestUrlBuilder::build($this->host, $path, $params);
        $psrRequest = $this->requestFactory->createRequest('GET', $url);

        try {
            $response = $this->httpClient->sendRequest($psrRequest);
        } catch (ClientExceptionInterface $e) {
            throw new RequestFailed($e->getMessage(), (int) $e->getCode(), $e);
        }

        try {
            $body = (string) $response->getBody();
        } catch (\RuntimeException $e) {
            throw new RequestFailed('Failed to read response body: ' . $e->getMessage(), (int) $e->getCode(), $e);
        }

        return ClientResponse::fromBody($body, $response->getStatusCode());
    }
}
