<?php

namespace App\Stellar\Soroban\Contract;

use App\Entity\Contract;
use App\Entity\User;
use App\Stellar\Soroban\AccountManager;
use App\Stellar\Soroban\Networks;
use App\Stellar\Soroban\ServerManager;
use App\Stellar\Soroban\Transaction\SorobanTransactionManager;
use Doctrine\ORM\EntityManagerInterface;
use Soneso\StellarSDK\Crypto\KeyPair;
use Soneso\StellarSDK\InvokeContractHostFunction;
use Soneso\StellarSDK\InvokeHostFunctionOperationBuilder;
use Soneso\StellarSDK\Soroban\Address;
use Soneso\StellarSDK\TransactionBuilder;
use Soneso\StellarSDK\Xdr\XdrInt128Parts;
use Soneso\StellarSDK\Xdr\XdrSCVal;

class InteractManager {

    public function __construct(
        private readonly AccountManager $accountManager,
        private readonly ServerManager $serverManager,
        private readonly SorobanTransactionManager $sorobanTransactionManager,
        private readonly EntityManagerInterface $em
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
            XdrSCVal::forI128(new XdrInt128Parts($amount, $amount)),
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

        $contract->setBalance((float)$resultValue->getI128()->getHi());
        $this->em->persist($contract);
        $this->em->flush();
        return $contract->getBalance();
    }

    public function initToken(string $tokenAddress, int $decimal, string $name, string $symbol): void
    {
        $keyPairSubmiter = $this->accountManager->getSystemKeyPair();
        $accountSubmiter = $this->accountManager->getAccount($keyPairSubmiter);

        $invokeContractHostFunction = new InvokeContractHostFunction($tokenAddress, "initialize", [
            Address::fromAccountId($accountSubmiter->getAccountId())->toXdrSCVal(),
            XdrSCVal::forU32($decimal),
            XdrSCVal::forString($name),
            XdrSCVal::forString($symbol)
        ]);

        $builder = new InvokeHostFunctionOperationBuilder($invokeContractHostFunction);
        $operation = $builder->build();
        $transaction = (new TransactionBuilder($accountSubmiter))->addOperation($operation)->build();

        $server = $this->serverManager->getServer(Networks::TESTNET);
        $this->sorobanTransactionManager->simulate($server, $transaction, $keyPairSubmiter, true);

        $sendResponse = $server->sendTransaction($transaction);
        $transactionResponse = $this->sorobanTransactionManager->waitForTransaction($server, $sendResponse);

        $resultValue = $transactionResponse->getResultValue();
        if($resultValue->getError()) {
            throw new \RuntimeException('Token initialize call execution failed: ' . $resultValue->getError()->getCode()->getValue());
        }
    }

    public function mintUserWithToken(string $tokenAddress, User $user, int $amount): void
    {
        $keyPairSubmiter = $this->accountManager->getSystemKeyPair();
        $accountSubmiter = $this->accountManager->getAccount($keyPairSubmiter);
        $keyPairUser     = KeyPair::fromSeed($user->getSecret());
        $userAccount     = $this->accountManager->getAccount($keyPairUser);

        $invokeContractHostFunction = new InvokeContractHostFunction($tokenAddress, "mint", [
            Address::fromAccountId($userAccount->getAccountId())->toXdrSCVal(),
            XdrSCVal::forI128(new XdrInt128Parts($amount, $amount))
        ]);

        $builder = new InvokeHostFunctionOperationBuilder($invokeContractHostFunction);
        $operation = $builder->build();
        $transaction = (new TransactionBuilder($accountSubmiter))->addOperation($operation)->build();

        $server = $this->serverManager->getServer(Networks::TESTNET);
        $this->sorobanTransactionManager->simulate($server, $transaction, $keyPairSubmiter, true);

        $sendResponse = $server->sendTransaction($transaction);
        $transactionResponse = $this->sorobanTransactionManager->waitForTransaction($server, $sendResponse);

        $resultValue = $transactionResponse->getResultValue();
        if($resultValue->getError()) {
            throw new \RuntimeException('Token mint call execution failed: ' . $resultValue->getError()->getCode()->getValue());
        }
    }
}