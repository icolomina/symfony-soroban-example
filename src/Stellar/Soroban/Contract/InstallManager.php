<?php 

namespace App\Stellar\Soroban\Contract;

use App\Entity\Contract;
use App\Entity\Token;
use App\Entity\User;
use App\Stellar\Soroban\AccountManager;
use App\Stellar\Soroban\Networks;
use App\Stellar\Soroban\ServerManager;
use App\Stellar\Soroban\Transaction\SorobanTransactionManager;
use Soneso\StellarSDK\CreateContractHostFunction;
use Soneso\StellarSDK\InvokeHostFunctionOperationBuilder;
use Soneso\StellarSDK\Soroban\Address;
use Soneso\StellarSDK\TransactionBuilder;

class InstallManager {

    public function __construct(
        private readonly string $wasmId,
        private readonly ServerManager $serverManager,
        private readonly SorobanTransactionManager $sorobanTransactionManager,
        private readonly AccountManager $accountManager
    ){ }

    public function installContract(): string
    {
        $keyPair = $this->accountManager->getSystemKeyPair();
        $account = $this->accountManager->getAccount($keyPair);

        $createContractHostFunction = new CreateContractHostFunction(Address::fromAccountId($account->getAccountId()), $this->wasmId);
        $builder = new InvokeHostFunctionOperationBuilder($createContractHostFunction);
        $operation = $builder->build();   
        $transaction = (new TransactionBuilder($account))->addOperation($operation)->build();

        $server = $this->serverManager->getServer(Networks::TESTNET);
        $this->sorobanTransactionManager->simulate($server, $transaction, $keyPair, true);

        $sendResponse = $server->sendTransaction($transaction);
        $transactionResponse = $this->sorobanTransactionManager->waitForTransaction($server, $sendResponse);

        return $transactionResponse->getCreatedContractId();

    }
}