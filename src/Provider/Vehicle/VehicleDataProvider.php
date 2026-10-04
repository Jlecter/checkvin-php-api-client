<?php

declare(strict_types=1);

namespace CheckVin\Api\Provider\Vehicle;

use CheckVin\Api\Config\ApiUriGlossary;
use CheckVin\Api\Http\Response\ApiResponse;
use CheckVin\Api\Provider\AbstractDataProvider;

final class VehicleDataProvider extends AbstractDataProvider implements VehicleDataProviderInterface
{
    public function getInfo(string $vinCode): ApiResponse
    {
        return $this->call(ApiUriGlossary::VEHICLE_INFO_PATH, [self::QUERY_PARAM_VIN_CODE => $this->vinCode($vinCode)]);
    }
}
