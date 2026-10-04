<?php
declare(strict_types=1);

namespace CheckVin\Api\Provider\Autocheck;

use CheckVin\Api\Http\Response\ApiResponse;

/**
 * Interface AutoCheckDataProviderInterface.
 */
interface AutoCheckDataProviderInterface
{
    /**
     * @param string $vinCode
     *
     * @return ApiResponse
     */
    public function getAutoCheckForVinCode(string $vinCode): ApiResponse;
    
    /**
     * @param string $vinCode
     *
     * @return ApiResponse
     */
    public function checkReportExists(string $vinCode): ApiResponse;
}
