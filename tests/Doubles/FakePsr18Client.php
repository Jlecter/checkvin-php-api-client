<?php

declare(strict_types=1);

namespace CheckVin\Api\Tests\Doubles;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class FakePsr18Client implements ClientInterface
{
    private ?RequestInterface $lastRequest = null;
    private ?ResponseInterface $stubbedResponse = null;
    private ?ClientExceptionInterface $stubbedException = null;

    public function stubResponse(ResponseInterface $response): void
    {
        $this->stubbedResponse = $response;
        $this->stubbedException = null;
    }

    public function stubException(ClientExceptionInterface $exception): void
    {
        $this->stubbedException = $exception;
        $this->stubbedResponse = null;
    }

    public function getLastRequest(): ?RequestInterface
    {
        return $this->lastRequest;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->lastRequest = $request;

        if ($this->stubbedException !== null) {
            throw $this->stubbedException;
        }

        if ($this->stubbedResponse === null) {
            throw new \LogicException('FakePsr18Client: no response or exception configured.');
        }

        return $this->stubbedResponse;
    }
}
