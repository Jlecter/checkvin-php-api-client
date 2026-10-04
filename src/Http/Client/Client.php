<?php

declare(strict_types=1);

namespace CheckVin\Api\Http\Client;

use CheckVin\Api\Config\Config;
use CheckVin\Api\Exception\RequestFailed;
use CheckVin\Api\Http\Response\ClientResponse;

final class Client implements ClientInterface
{
    public function __construct(private readonly Config $config)
    {
    }

    public function request(string $path, #[\SensitiveParameter] array $params): ClientResponse
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, RequestUrlBuilder::build($this->config->getHost(), $path, $params));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, $this->config->getConnectTimeoutMs());
        curl_setopt($curl, CURLOPT_TIMEOUT_MS, $this->config->getTimeoutMs());
        curl_setopt($curl, CURLOPT_NOSIGNAL, 1);

        $output = curl_exec($curl);

        if ($output === false) {
            $errno = curl_errno($curl);
            $error = curl_error($curl);
            throw new RequestFailed(
                sprintf('Request failed (errno %d): %s', $errno, $error),
                $errno,
            );
        }

        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);

        return ClientResponse::fromBody($output, $httpCode);
    }
}
