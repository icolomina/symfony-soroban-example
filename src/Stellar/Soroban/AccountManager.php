<?php

namespace App\Stellar\Soroban;

use Soneso\StellarSDK\Crypto\KeyPair;
use Soneso\StellarSDK\Responses\Account\AccountResponse;
use Soneso\StellarSDK\StellarSDK;

class AccountManager {

    public function __construct(
        private readonly string $secret
    ){ }

    public function getSystemKeyPair(): KeyPair
    {
        return KeyPair::fromSeed($this->secret);
    }

    public function getAccount(KeyPair $keyPair): AccountResponse
    {
        return StellarSDK::getTestNetInstance()->requestAccount($keyPair->getAccountId());
    }
}