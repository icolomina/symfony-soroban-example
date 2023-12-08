<?php

namespace App\Stellar\Soroban\Transaction;

use Soneso\StellarSDK\Crypto\KeyPair;
use Soneso\StellarSDK\Network;
use Soneso\StellarSDK\Soroban\Responses\GetTransactionResponse;
use Soneso\StellarSDK\Soroban\Responses\SendTransactionResponse;
use Soneso\StellarSDK\Soroban\SorobanServer;
use Soneso\StellarSDK\Transaction;

class SorobanTransactionManager {

    public function simulate(SorobanServer $server, Transaction $transaction, KeyPair $keyPair, bool $addAuth = false): void
    {
        $simulateResponse = $server->simulateTransaction($transaction);

        if($simulateResponse->getError()) {
            throw new \RuntimeException('Transaction simulation error: ' . $simulateResponse->getError());
        }

        $transactionData = $simulateResponse->transactionData;
        $minResourceFee = $simulateResponse->minResourceFee;

        $transaction->setSorobanTransactionData($transactionData);
        $transaction->addResourceFee($minResourceFee);
        if($addAuth) {
            $transaction->setSorobanAuth($simulateResponse->getSorobanAuth());
        }
        $transaction->sign($keyPair, Network::testnet());
    }

    public function waitForTransaction(SorobanServer $server, SendTransactionResponse $sendResponse): GetTransactionResponse
    {
        if ($sendResponse->getError()) {
            throw new \RuntimeException('Transaction Error: ' . $sendResponse->getError());
        }

        do{
            sleep(1);
            $transactionResponse = $server->getTransaction($sendResponse->hash);
            $status = $transactionResponse->status;

        } while(!in_array($status, [GetTransactionResponse::STATUS_SUCCESS, GetTransactionResponse::STATUS_FAILED]));

        if($status === GetTransactionResponse::STATUS_FAILED) {
            dump($transactionResponse);die;
            throw new \RuntimeException('Transaction Error: ' . $transactionResponse->getError());
        }

        return $transactionResponse;
    }
}