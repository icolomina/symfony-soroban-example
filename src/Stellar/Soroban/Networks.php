<?php

namespace App\Stellar\Soroban;

enum Networks: string {

    case FUTURENET = 'https://rpc-futurenet.stellar.org:443';
    case TESTNET   = 'https://soroban-testnet.stellar.org:443';
}