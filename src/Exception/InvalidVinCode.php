<?php

declare(strict_types=1);

namespace CheckVin\Api\Exception;

final class InvalidVinCode extends \InvalidArgumentException implements CheckVinApiException
{
}
