<?php

namespace App\Stellar\Soroban;

use Soneso\StellarSDK\Soroban\Responses\GetHealthResponse;
use Soneso\StellarSDK\Soroban\SorobanServer;

class ServerManager {

    public function getServer(Networks $network): SorobanServer
    {
        $server = new SorobanServer($network->value);
        $healthResponse = $server->getHealth();
        if (GetHealthResponse::HEALTHY != $healthResponse->status) {
            throw new \RuntimeException(sprintf('Soroban server "%s" is not available', $network->value));
        }

        return $server;

    }
}