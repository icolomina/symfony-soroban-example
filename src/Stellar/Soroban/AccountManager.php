<?php

namespace App\Stellar\Soroban;

use App\Entity\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Soneso\StellarSDK\Crypto\KeyPair;
use Soneso\StellarSDK\Responses\Account\AccountResponse;
use Soneso\StellarSDK\StellarSDK;
use Soneso\StellarSDK\Util\FriendBot;

class AccountManager {

    public function __construct(
        private readonly EntityManagerInterface $em
    ){ }

    public function getSystemKeyPair(): KeyPair
    {
        $configuration = $this->em->getRepository(Configuration::class)->findOneBy(['configKey' => 'stellar_sys_secret']);
        if($configuration) {
            $secret = $configuration->getConfigValue();
            return KeyPair::fromSeed($secret);
        }

        $keyPair = KeyPair::random();
        FriendBot::fundTestAccount($keyPair->getAccountId());
        $configuration = new Configuration();
        $configuration->setConfigKey('stellar_sys_secret');
        $configuration->setConfigValue($keyPair->getSecretSeed());

        $this->em->persist($configuration);
        $this->em->flush();

        return $keyPair;
    }

    public function getAccount(KeyPair $keyPair): AccountResponse
    {
        return StellarSDK::getTestNetInstance()->requestAccount($keyPair->getAccountId());
    }
}