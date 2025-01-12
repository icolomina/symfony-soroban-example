<?php

namespace App\Stellar\Soroban\Transaction;

use Soneso\StellarSDK\Crypto\KeyPair;
use Soneso\StellarSDK\Network;
use Soneso\StellarSDK\Soroban\Requests\SimulateTransactionRequest;
use Soneso\StellarSDK\Soroban\Responses\GetTransactionResponse;
use Soneso\StellarSDK\Soroban\Responses\SendTransactionResponse;
use Soneso\StellarSDK\Soroban\SorobanAuthorizationEntry;
use Soneso\StellarSDK\Soroban\SorobanServer;
use Soneso\StellarSDK\Transaction;

class SorobanTransactionManager {

    public function simulate(SorobanServer $server, Transaction $transaction, KeyPair $keyPair, bool $addAuth = false, ?KeyPair $invoker = null): void
    {
        $request = new SimulateTransactionRequest($transaction);
        $simulateResponse = $server->simulateTransaction($request);

        if($simulateResponse->getError()) {
            throw new \RuntimeException('Transaction simulation error: ' . $simulateResponse->getError());
        }

        $transactionData = $simulateResponse->transactionData;
        $minResourceFee = $simulateResponse->minResourceFee;

        $transaction->setSorobanTransactionData($transactionData);
        $transaction->addResourceFee($minResourceFee);
        if($addAuth) {
            if($invoker) {
                $auth = $simulateResponse->getSorobanAuth();
                $latestLedgerResponse = $server->getLatestLedger();
                foreach ($auth as $a) {
                    if ($a instanceof  SorobanAuthorizationEntry) {
                        $a->credentials->addressCredentials->signatureExpirationLedger = $latestLedgerResponse->sequence + 10;
                        $a->sign($invoker, Network::testnet());
                    }
                }

                $transaction->setSorobanAuth($auth);
            }
            else{
                $transaction->setSorobanAuth($simulateResponse->getSorobanAuth());
            }
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
            dump($transactionResponse);
            throw new \RuntimeException('Transaction Error: ' . $transactionResponse->getError());
        }

        return $transactionResponse;
    }
}