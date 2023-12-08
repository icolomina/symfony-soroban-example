<?php

namespace App\Stellar\Soroban\Contract;

use App\Stellar\Soroban\Networks;
use App\Stellar\Soroban\ServerManager;
use App\Stellar\Soroban\Transaction\SorobanTransactionManager;
use Soneso\StellarSDK\Crypto\KeyPair;
use Soneso\StellarSDK\InvokeHostFunctionOperationBuilder;
use Soneso\StellarSDK\Responses\Account\AccountResponse;
use Soneso\StellarSDK\TransactionBuilder;
use Soneso\StellarSDK\UploadContractWasmHostFunction;

class DeployManager {

    public function __construct(
        private readonly ServerManager $serverManager,
        private readonly SorobanTransactionManager $sorobanTransactionManager
    ){}

    public function deployContract(string $wasmCode, KeyPair $keyPair, AccountResponse $account): string
    {

        $uploadContractHostFunction = new UploadContractWasmHostFunction($wasmCode);
        $builder = new InvokeHostFunctionOperationBuilder($uploadContractHostFunction);
        $operation = $builder->build();

        $transaction = (new TransactionBuilder($account))->addOperation($operation)->build();

        $server = $this->serverManager->getServer(Networks::TESTNET);
        $this->sorobanTransactionManager->simulate($server, $transaction, $keyPair);

        $sendResponse = $server->sendTransaction($transaction);
        $transactionResponse = $this->sorobanTransactionManager->waitForTransaction($server, $sendResponse);

        return $transactionResponse->getWasmId();

    }

       

}