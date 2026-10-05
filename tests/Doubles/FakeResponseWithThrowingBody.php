<?php

declare(strict_types=1);

namespace CheckVin\Api\Tests\Doubles;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * A ResponseInterface stub that returns a ThrowingBodyStream from getBody(),
 * delegating every other method to a real Nyholm Response.
 */
final class FakeResponseWithThrowingBody implements ResponseInterface
{
    private readonly Response $inner;
    private readonly ThrowingBodyStream $stream;

    public function __construct(int $statusCode = 200)
    {
        $this->inner = new Response($statusCode);
        $this->stream = new ThrowingBodyStream();
    }

    public function getBody(): StreamInterface
    {
        return $this->stream;
    }

    public function getStatusCode(): int
    {
        return $this->inner->getStatusCode();
    }

    public function getProtocolVersion(): string
    {
        return $this->inner->getProtocolVersion();
    }

    public function withProtocolVersion(string $version): static
    {
        return $this;
    }

    public function getHeaders(): array
    {
        return $this->inner->getHeaders();
    }

    public function hasHeader(string $name): bool
    {
        return $this->inner->hasHeader($name);
    }

    public function getHeader(string $name): array
    {
        return $this->inner->getHeader($name);
    }

    public function getHeaderLine(string $name): string
    {
        return $this->inner->getHeaderLine($name);
    }

    public function withHeader(string $name, $value): static
    {
        return $this;
    }

    public function withAddedHeader(string $name, $value): static
    {
        return $this;
    }

    public function withoutHeader(string $name): static
    {
        return $this;
    }

    public function withBody(StreamInterface $body): static
    {
        return $this;
    }

    public function withStatus(int $code, string $reasonPhrase = ''): static
    {
        return $this;
    }

    public function getReasonPhrase(): string
    {
        return $this->inner->getReasonPhrase();
    }
}
