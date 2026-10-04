<?php

declare(strict_types=1);

namespace CheckVin\Api\Http\Client;

use CheckVin\Api\Http\Response\ClientResponse;

interface ClientInterface
{
    public function request(string $path, #[\SensitiveParameter] array $params): ClientResponse;
}
