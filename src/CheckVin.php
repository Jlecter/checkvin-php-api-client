<?php

declare(strict_types=1);

namespace CheckVin\Api;

use CheckVin\Api\Config\Config;
use CheckVin\Api\Http\Client\Client;
use CheckVin\Api\Http\Client\ClientInterface;
use CheckVin\Api\Provider\Autocheck\AutocheckDataProvider;
use CheckVin\Api\Provider\Autocheck\AutocheckDataProviderInterface;
use CheckVin\Api\Provider\Balance\BalanceDataProvider;
use CheckVin\Api\Provider\Balance\BalanceDataProviderInterface;
use CheckVin\Api\Provider\Carfax\CarfaxDataProvider;
use CheckVin\Api\Provider\Carfax\CarfaxDataProviderInterface;
use CheckVin\Api\Provider\Vehicle\VehicleDataProvider;
use CheckVin\Api\Provider\Vehicle\VehicleDataProviderInterface;

final class CheckVin
{
    private readonly ClientInterface $client;

    private ?AutocheckDataProviderInterface $autocheck = null;
    private ?CarfaxDataProviderInterface $carfax = null;
    private ?BalanceDataProviderInterface $balance = null;
    private ?VehicleDataProviderInterface $vehicle = null;

    public function __construct(
        #[\SensitiveParameter] private readonly string $apiKey,
        ?ClientInterface $client = null,
    ) {
        $this->client = $client ?? new Client(new Config());
    }

    public function autocheck(): AutocheckDataProviderInterface
    {
        return $this->autocheck ??= new AutocheckDataProvider($this->apiKey, $this->client);
    }

    public function carfax(): CarfaxDataProviderInterface
    {
        return $this->carfax ??= new CarfaxDataProvider($this->apiKey, $this->client);
    }

    public function balance(): BalanceDataProviderInterface
    {
        return $this->balance ??= new BalanceDataProvider($this->apiKey, $this->client);
    }

    public function vehicle(): VehicleDataProviderInterface
    {
        return $this->vehicle ??= new VehicleDataProvider($this->apiKey, $this->client);
    }
}
