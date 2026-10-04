<?php

declare(strict_types=1);

namespace CheckVin\Api\Tests\Doubles;

use Psr\Http\Client\ClientExceptionInterface;

final class FakeClientException extends \RuntimeException implements ClientExceptionInterface
{
}
