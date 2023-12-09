<?php 

namespace App\Command\Stellar;

use Soneso\StellarSDK\Crypto\StrKey;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name : 'address:encode'
)]
class EncodeHexAddressCommand extends Command
{

    public function configure()
    {
        $this
            ->addArgument('addr', null, InputOption::VALUE_REQUIRED, 'Hex address')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $encdedAddr = StrKey::encodeContractIdHex($input->getArgument('addr'));
        $output->writeln($encdedAddr);

        return Command::SUCCESS;
    }

}