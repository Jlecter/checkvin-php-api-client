<?php

declare(strict_types=1);

namespace CheckVin\Api\Config;

final class ApiUriGlossary
{
    public const VIN_AUTOCHECK_PATH = '/api/v1/autocheck';
    public const VIN_AUTOCHECK_REPORT_EXIST_PATH = '/api/v1/autocheck/check';
    public const VIN_CARFAX_PATH = '/api/v1/carfax';
    public const VIN_CARFAX_REPORT_EXIST_PATH = '/api/v1/carfax/check';
    public const CHECK_BALANCE_PATH = '/api/v1/carfax/balance';
    public const VEHICLE_INFO_PATH = '/api/v1/vehicle/info';
}
