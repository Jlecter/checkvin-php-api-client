<?php

declare(strict_types=1);

namespace CheckVin\Api\Tests;

use CheckVin\Api\Config\ApiUriGlossary;
use CheckVin\Api\Config\Config;
use CheckVin\Api\Exception\RequestFailed;
use CheckVin\Api\Http\Client\Psr18Client;
use CheckVin\Api\Provider\Balance\BalanceDataProvider;
use CheckVin\Api\Tests\Doubles\FakeClientException;
use CheckVin\Api\Tests\Doubles\FakePsr18Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class Psr18ClientTest extends TestCase
{
    private FakePsr18Client $fakeHttp;
    private Psr17Factory $factory;
    private Psr18Client $client;

    protected function setUp(): void
    {
        $this->fakeHttp = new FakePsr18Client();
        $this->factory = new Psr17Factory();
        $this->client = new Psr18Client($this->fakeHttp, $this->factory);
    }

    public function testBuildsCorrectUrlWithPathAndQueryParams(): void
    {
        // Arrange
        $this->fakeHttp->stubResponse(new Response(200, [], '{}'));

        // Action
        $this->client->request('/api/v1/test', ['api_key' => 'abc', 'foo' => 'bar baz']);

        // Assert
        $uri = (string) $this->fakeHttp->getLastRequest()->getUri();
        self::assertStringContainsString('/api/v1/test', $uri);
        self::assertStringContainsString('api_key=abc', $uri);
        // RFC3986: space encoded as %20, not +
        self::assertStringContainsString('foo=bar%20baz', $uri);
    }

    public function testDefaultHostMatchesConfig(): void
    {
        // Arrange
        $this->fakeHttp->stubResponse(new Response(200, [], '{}'));

        // Action
        $this->client->request('/path', []);

        // Assert
        $uri = (string) $this->fakeHttp->getLastRequest()->getUri();
        self::assertStringStartsWith(Config::DEFAULT_HOST, $uri);
    }

    public function testTrailingSlashOnHostIsStripped(): void
    {
        // Arrange
        $clientWithSlash = new Psr18Client($this->fakeHttp, $this->factory, 'https://example.com/');
        $this->fakeHttp->stubResponse(new Response(200, [], '{}'));

        // Action
        $clientWithSlash->request('/path', []);

        // Assert
        $uri = (string) $this->fakeHttp->getLastRequest()->getUri();
        self::assertStringStartsWith('https://example.com/', $uri);
        self::assertStringNotContainsString('https://example.com//', $uri);
    }

    public function testCustomHostIsUsed(): void
    {
        // Arrange
        $client = new Psr18Client($this->fakeHttp, $this->factory, 'https://custom.host');
        $this->fakeHttp->stubResponse(new Response(200, [], '{}'));

        // Action
        $client->request('/test', []);

        // Assert
        $uri = (string) $this->fakeHttp->getLastRequest()->getUri();
        self::assertStringStartsWith('https://custom.host', $uri);
    }

    public function testStatusCodeIsMappedToClientResponse(): void
    {
        // Arrange
        $this->fakeHttp->stubResponse(new Response(401, [], '{"message":"Unauthorized"}'));

        // Action
        $response = $this->client->request('/path', []);

        // Assert
        self::assertSame(401, $response->getResponseHttpCode());
    }

    public function testBodyIsParsedIntoClientResponse(): void
    {
        // Arrange
        $this->fakeHttp->stubResponse(new Response(200, [], '{"balance":99}'));

        // Action
        $response = $this->client->request('/path', []);

        // Assert
        self::assertSame(99, $response->getData()['balance']);
        self::assertTrue($response->hasValidBody());
    }

    public function testMalformedBodyProducesInvalidClientResponse(): void
    {
        // Arrange — JSON list is rejected by ClientResponse as malformed
        $this->fakeHttp->stubResponse(new Response(200, [], '[1,2,3]'));

        // Action
        $response = $this->client->request('/path', []);

        // Assert
        self::assertFalse($response->hasValidBody());
    }

    public function testNonJsonBodyProducesInvalidClientResponse(): void
    {
        // Arrange
        $this->fakeHttp->stubResponse(new Response(502, [], '<html>Bad Gateway</html>'));

        // Action
        $response = $this->client->request('/path', []);

        // Assert
        self::assertFalse($response->hasValidBody());
        self::assertSame(502, $response->getResponseHttpCode());
    }

    public function testTransportExceptionIsWrappedInRequestFailed(): void
    {
        // Arrange
        $this->fakeHttp->stubException(new FakeClientException('connection refused', 7));

        // Action + Assert
        $this->expectException(RequestFailed::class);
        $this->expectExceptionMessage('connection refused');
        $this->client->request('/path', []);
    }

    public function testOriginalExceptionIsAvailableAsPrevious(): void
    {
        // Arrange
        $original = new FakeClientException('timeout', 28);
        $this->fakeHttp->stubException($original);

        // Action
        $caught = null;
        try {
            $this->client->request('/path', []);
        } catch (RequestFailed $e) {
            $caught = $e;
        }

        // Assert
        self::assertNotNull($caught);
        self::assertSame($original, $caught->getPrevious());
    }

    public function testUsesGetMethod(): void
    {
        // Arrange
        $this->fakeHttp->stubResponse(new Response(200, [], '{}'));

        // Action
        $this->client->request('/path', []);

        // Assert
        self::assertSame('GET', $this->fakeHttp->getLastRequest()->getMethod());
    }

    public function testEndToEndWithBalanceDataProvider(): void
    {
        // Arrange
        $this->fakeHttp->stubResponse(new Response(200, [], '{"balance":42}'));
        $provider = new BalanceDataProvider('my-key', $this->client);

        // Action
        $response = $provider->getBalance();

        // Assert
        self::assertTrue($response->isSuccess());
        self::assertSame(42, $response->getData()['balance']);

        $uri = (string) $this->fakeHttp->getLastRequest()->getUri();
        self::assertStringContainsString(ApiUriGlossary::CHECK_BALANCE_PATH, $uri);
        self::assertStringContainsString('api_key=my-key', $uri);
    }

    public function testEndToEndProviderReturnsErrorOnTransportException(): void
    {
        // Arrange
        $this->fakeHttp->stubException(new FakeClientException('network error'));
        $provider = new BalanceDataProvider('my-key', $this->client);

        // Action + Assert
        $this->expectException(RequestFailed::class);
        $provider->getBalance();
    }
}
