<?php
declare(strict_types=1);

namespace CheckVin\Api\Provider\Carfax;

use CheckVin\Api\Http\Response\ApiResponse;

/**
 * Interface CarfaxDataProviderInterface.
 */
interface CarfaxDataProviderInterface
{
    /**
     * @param string $vinCode
     *
     * @return ApiResponse
     */
    public function getCarfaxForVinCode(string $vinCode): ApiResponse;
    
    /**
     * @param string $vinCode
     *
     * @return ApiResponse
     */
    public function checkReportExists(string $vinCode): ApiResponse;
}
