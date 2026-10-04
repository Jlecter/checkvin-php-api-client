<?php

declare(strict_types=1);

namespace CheckVin\Api\Provider\Autocheck;

use CheckVin\Api\Http\Response\ApiResponse;

interface AutocheckDataProviderInterface
{
    public function getAutoCheckForVinCode(string $vinCode): ApiResponse;

    public function checkReportExists(string $vinCode): ApiResponse;
}
