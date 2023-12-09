<?php

namespace App\Command\Stellar;

use App\Contract\ContractManager;
use App\Stellar\Soroban\AccountManager;
use App\Stellar\Soroban\Contract\DeployManager;
use App\Stellar\Soroban\Contract\InstallManager;
use App\Stellar\Soroban\Contract\WasmManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name : 'contract:deploy'
)]
class ContractDeployerCommand extends Command
{
    public function __construct(
        private readonly AccountManager $accountManager,
        private readonly DeployManager $deployManager,
        private readonly InstallManager $installManager,
        private readonly WasmManager $wasmManager,
        private readonly ContractManager $contractManager
    ){
        parent::__construct();
    }

    public function configure()
    {
        $this
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Wams type', 'contract')
            ->addOption('install', null, InputOption::VALUE_NONE, 'Install too')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $type = $input->getOption('type');
        $keyPair = $this->accountManager->getSystemKeyPair();
        $account = $this->accountManager->getAccount($keyPair);
        $installToo = $input->getOption('install');

        $wamsCode = ($type === 'contract') ? $this->wasmManager->getWamsCode() : $this->wasmManager->getTokenCode();
        $wasmId = $this->deployManager->deployContract($wamsCode, $keyPair, $account);
        $output->writeln('Wasm id: ' . $wasmId);

        if($installToo) {
            $id = $this->installManager->installContract($wasmId);
            $output->writeln('Contract id: ' . $id);
        }

        return Command::SUCCESS;
    }
}