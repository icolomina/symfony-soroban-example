<?php

namespace App\Command\Stellar;

use App\Stellar\Soroban\AccountManager;
use App\Stellar\Soroban\Contract\DeployManager;
use App\Stellar\Soroban\Contract\WasmManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name : 'contract:deploy'
)]
class ContractDeployerCommand extends Command
{
    public function __construct(
        private readonly AccountManager $accountManager,
        private readonly DeployManager $deployManager,
        private readonly WasmManager $wasmManager
    ){
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $keyPair = $this->accountManager->getSystemKeyPair();
        $account = $this->accountManager->getAccount($keyPair);

        $wasmId = $this->deployManager->deployContract($this->wasmManager->getWamsCode(), $keyPair, $account);
        $output->writeln('Wasm id: ' . $wasmId);
        return Command::SUCCESS;
    }
}