<?php

declare(strict_types=1);

namespace CheckVin\Api\Http\Client;

final class RequestUrlBuilder
{
    private function __construct()
    {
    }

    public static function build(string $host, string $path, array $params): string
    {
        return $host . $path . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }
}
