<?php

declare(strict_types=1);

namespace CheckVin\Api\Provider\Balance;

use CheckVin\Api\Http\Response\ApiResponse;

interface BalanceDataProviderInterface
{
    public function getBalance(): ApiResponse;
}
