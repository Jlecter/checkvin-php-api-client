<?php

declare(strict_types=1);

namespace CheckVin\Api\Provider\Vehicle;

use CheckVin\Api\Http\Response\Abstraction\ApiResponse;

interface VehicleDataProviderInterface
{
    public function getInfo(string $vinCode): ApiResponse;
}
