<?php

namespace App\Stellar\Soroban\Contract;

use App\Entity\Contract;
use App\Stellar\Soroban\AccountManager;
use App\Stellar\Soroban\Networks;
use App\Stellar\Soroban\ServerManager;
use App\Stellar\Soroban\Transaction\SorobanTransactionManager;
use Soneso\StellarSDK\Crypto\KeyPair;
use Soneso\StellarSDK\Crypto\StrKey;
use Soneso\StellarSDK\InvokeContractHostFunction;
use Soneso\StellarSDK\InvokeHostFunctionOperationBuilder;
use Soneso\StellarSDK\Soroban\Address;
use Soneso\StellarSDK\TransactionBuilder;
use Soneso\StellarSDK\Xdr\XdrInt128Parts;
use Soneso\StellarSDK\Xdr\XdrSCAddress;
use Soneso\StellarSDK\Xdr\XdrSCVal;

class InteractManager {

    public function __construct(
        private readonly AccountManager $accountManager,
        private readonly string $secret,
        private readonly ServerManager $serverManager,
        private readonly SorobanTransactionManager $sorobanTransactionManager,
    ){}

    public function initContract(Contract $contract): bool
    {
        $keyPair = $this->accountManager->getSystemKeyPair();
        $account = $this->accountManager->getAccount($keyPair);

        $invokeContractHostFunction = new InvokeContractHostFunction($contract->getAddress(), "init", [
            Address::fromAccountId($account->getAccountId())->toXdrSCVal(),
            Address::fromContractId($contract->getToken()->getAddress())->toXdrSCVal()
        ]);

        $builder = new InvokeHostFunctionOperationBuilder($invokeContractHostFunction);
        $operation = $builder->build();
        $transaction = (new TransactionBuilder($account))->addOperation($operation)->build();

        $server = $this->serverManager->getServer(Networks::TESTNET);
        $this->sorobanTransactionManager->simulate($server, $transaction, $keyPair, true);

        $sendResponse = $server->sendTransaction($transaction);
        $transactionResponse = $this->sorobanTransactionManager->waitForTransaction($server, $sendResponse);

        $resultValue = $transactionResponse->getResultValue();
        if($resultValue->getError()) {
            throw new \RuntimeException('Contract call execution failed: ' . $resultValue->getError()->getCode()->getValue());
        }

        return $resultValue->getB();
    }

    public function depositInContract(Contract $contract, int $amount): int
    {
        $keyPairSubmiter = $this->accountManager->getSystemKeyPair();
        $keyPairInvoker  = KeyPair::fromSeed($contract->getSender()->getSecret());
        $accountSubmiter = $this->accountManager->getAccount($keyPairSubmiter);
        $accountInvoker  = $this->accountManager->getAccount($keyPairInvoker);

        $invokeContractHostFunction = new InvokeContractHostFunction($contract->getAddress(), "deposit", [
            Address::fromAccountId($accountInvoker->getAccountId())->toXdrSCVal(),
            XdrSCVal::forI128(new XdrInt128Parts($amount, 0)),
        ]);

        $builder = new InvokeHostFunctionOperationBuilder($invokeContractHostFunction);
        $operation = $builder->build();
        $transaction = (new TransactionBuilder($accountSubmiter))->addOperation($operation)->build();

        $server = $this->serverManager->getServer(Networks::TESTNET);
        $this->sorobanTransactionManager->simulate($server, $transaction, $keyPairSubmiter, true, $keyPairInvoker);

        $sendResponse = $server->sendTransaction($transaction);
        $transactionResponse = $this->sorobanTransactionManager->waitForTransaction($server, $sendResponse);

        $resultValue = $transactionResponse->getResultValue();
        if($resultValue->getError()) {
            throw new \RuntimeException('Contract call execution failed: ' . $resultValue->getError()->getCode()->getValue());
        }

        return $resultValue->getI128()->getHi();
    }
}