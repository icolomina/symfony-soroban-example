<?php

namespace App\Stellar\Soroban\Contract;

use App\Entity\Contract;
use App\Stellar\Soroban\AccountManager;
use App\Stellar\Soroban\Networks;
use App\Stellar\Soroban\ServerManager;
use App\Stellar\Soroban\Transaction\SorobanTransactionManager;
use Soneso\StellarSDK\Crypto\KeyPair;
use Soneso\StellarSDK\InvokeContractHostFunction;
use Soneso\StellarSDK\InvokeHostFunctionOperationBuilder;
use Soneso\StellarSDK\Soroban\Address;
use Soneso\StellarSDK\TransactionBuilder;

class InteractManager {

    public function __construct(
        private readonly AccountManager $accountManager,
        private readonly string $secret,
        private readonly ServerManager $serverManager,
        private readonly SorobanTransactionManager $sorobanTransactionManager,
    ){}

    public function initContract(Contract $contract): mixed
    {
        $keyPair = $this->accountManager->getSystemKeyPair();
        $account = $this->accountManager->getAccount($keyPair);

        $senderKp = KeyPair::fromPublicKey($contract->getSender()->getAddress());
        $receiverKp = KeyPair::fromPublicKey($contract->getReceiver()->getAddress());
        $tokenKp = KeyPair::fromPublicKey($contract->getToken()->getAddress());

        $invokeContractHostFunction = new InvokeContractHostFunction($contract->getAddress(), "init", [
            Address::fromAccountId($account->getAccountId()),
            Address::fromAccountId($senderKp->getAccountId()),
            Address::fromAccountId($receiverKp->getAccountId()),
            Address::fromAccountId($tokenKp->getAccountId())
        ]);

        $builder = new InvokeHostFunctionOperationBuilder($invokeContractHostFunction);
        $operation = $builder->build();
        $transaction = (new TransactionBuilder($account))->addOperation($operation)->build();

        $server = $this->serverManager->getServer(Networks::TESTNET);
        $this->sorobanTransactionManager->simulate($server, $transaction, $keyPair, true);

        $sendResponse = $server->sendTransaction($transaction);
        $transactionResponse = $this->sorobanTransactionManager->waitForTransaction($server, $sendResponse);

        return $transactionResponse->getResultValue();
    }
}