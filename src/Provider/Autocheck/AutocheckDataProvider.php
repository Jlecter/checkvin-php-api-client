<?php

declare(strict_types=1);

namespace CheckVin\Api\Provider\Autocheck;

use CheckVin\Api\Config\ApiUriGlossary;
use CheckVin\Api\Http\Response\ApiResponse;
use CheckVin\Api\Provider\AbstractDataProvider;

final class AutocheckDataProvider extends AbstractDataProvider implements AutocheckDataProviderInterface
{
    public function getAutoCheckForVinCode(string $vinCode): ApiResponse
    {
        return $this->call(ApiUriGlossary::VIN_AUTOCHECK_PATH, [self::QUERY_PARAM_VIN_CODE => $this->vinCode($vinCode)]);
    }

    public function checkReportExists(string $vinCode): ApiResponse
    {
        return $this->call(ApiUriGlossary::VIN_AUTOCHECK_REPORT_EXIST_PATH, [self::QUERY_PARAM_VIN_CODE => $this->vinCode($vinCode)]);
    }
}
