<?php

declare(strict_types=1);

namespace CheckVin\Api\Tests\Doubles;

use CheckVin\Api\Http\Client\ClientInterface;
use CheckVin\Api\Http\Response\ClientResponse;

final class StubClient implements ClientInterface
{
    private string $lastPath = '';
    private array $lastParams = [];
    private ?ClientResponse $stubbedResponse = null;
    private bool $requestCalled = false;

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

    public function wasRequestCalled(): bool
    {
        return $this->requestCalled;
    }

    public function request(string $path, #[\SensitiveParameter] array $params): ClientResponse
    {
        $this->requestCalled = true;
        $this->lastPath = $path;
        $this->lastParams = $params;

        return $this->stubbedResponse ?? new ClientResponse([], 200);
    }
}
