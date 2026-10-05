<?php

declare(strict_types=1);

namespace CheckVin\Api\Provider\Carfax;

use CheckVin\Api\Http\Response\ApiResponse;

interface CarfaxDataProviderInterface
{
    public function getCarfaxForVinCode(string $vinCode): ApiResponse;

    public function checkReportExists(string $vinCode): ApiResponse;
}
