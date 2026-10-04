<?php

declare(strict_types=1);

namespace CheckVin\Api\Provider\Balance;

use CheckVin\Api\Config\ApiUriGlossary;
use CheckVin\Api\Http\Response\ApiResponse;
use CheckVin\Api\Provider\AbstractDataProvider;

final class BalanceDataProvider extends AbstractDataProvider implements BalanceDataProviderInterface
{
    public function getBalance(): ApiResponse
    {
        return $this->call(ApiUriGlossary::CHECK_BALANCE_PATH);
    }
}
