<?php

declare(strict_types=1);

namespace CheckVin\Api\Provider\Carfax;

use CheckVin\Api\Config\ApiUriGlossary;
use CheckVin\Api\Http\Response\ApiResponse;
use CheckVin\Api\Provider\AbstractDataProvider;

final class CarfaxDataProvider extends AbstractDataProvider implements CarfaxDataProviderInterface
{
    public function getCarfaxForVinCode(string $vinCode): ApiResponse
    {
        return $this->call(ApiUriGlossary::VIN_CARFAX_PATH, [self::QUERY_PARAM_VIN_CODE => $this->vinCode($vinCode)]);
    }

    public function checkReportExists(string $vinCode): ApiResponse
    {
        return $this->call(ApiUriGlossary::VIN_CARFAX_REPORT_EXIST_PATH, [self::QUERY_PARAM_VIN_CODE => $this->vinCode($vinCode)]);
    }
}
