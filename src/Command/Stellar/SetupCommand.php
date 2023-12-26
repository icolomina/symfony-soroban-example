<?php

namespace App\Command\Stellar;

use App\Entity\Configuration;
use App\Entity\Token;
use App\Entity\User;
use App\Stellar\Soroban\AccountManager;
use App\Stellar\Soroban\Contract\DeployManager;
use App\Stellar\Soroban\Contract\InstallManager;
use App\Stellar\Soroban\Contract\InteractManager;
use App\Stellar\Soroban\Contract\WasmManager;
use Doctrine\ORM\EntityManagerInterface;
use Soneso\StellarSDK\Crypto\KeyPair;
use Soneso\StellarSDK\Util\FriendBot;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name : 'app:setup'
)]
class SetupCommand extends Command
{
    public function __construct(
        private readonly AccountManager $accountManager,
        private readonly DeployManager $deployManager,
        private readonly InstallManager $installManager,
        private readonly WasmManager $wasmManager,
        private readonly EntityManagerInterface $em,
        private readonly InteractManager $interactManager
    ){
        parent::__construct();
    }

    public function configure()
    {
        $this
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Wasm type', 'contract')
            ->addOption('install', null, InputOption::VALUE_NONE, 'Install too')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Deploying system contract ....');

        $keyPair = $this->accountManager->getSystemKeyPair();
        $account = $this->accountManager->getAccount($keyPair);
        $scWasmCode = $this->wasmManager->getWamsCode();

        $wasmId = $this->deployManager->deployContract($scWasmCode, $keyPair, $account);
        $output->writeln('System contract deployed - Wasm id: ' . $wasmId);

        $configuration = new Configuration();
        $configuration->setConfigKey('sc_wasm_id');
        $configuration->setConfigValue($wasmId);

        $this->em->persist($configuration);
        $this->em->flush();

        $output->writeln('Deploying token contract ....');
        $tokenWasmCode = $this->wasmManager->getTokenCode();
        $wasmTokenId = $this->deployManager->deployContract($tokenWasmCode, $keyPair, $account);

        $output->writeln('Token contract deployed - Wasm id: ' . $wasmTokenId);

        $output->writeln('Installing token contract ....');
        $id = $this->installManager->installContract($wasmTokenId);
        $output->writeln('Token contract installed. Token Contract id: ' . $id);

        $token = new Token();
        $token->setAddress($id);
        $token->setCreatedAt(new \DateTimeImmutable());
        $token->setEnabled(true);

        $code =  mb_strtoupper(substr(str_shuffle(uniqid($id)), 0, 4));
        $this->interactManager->initToken($id, 4, 'MyToken', $code);

        $token->setCode($code);
        $token->setName('MyToken');

        $this->em->persist($token);
        $this->em->flush();

        $output->writeln('Token contract initialized. Token Contract code: ' . $code);


        // Create and mint a user
        $keyPairUser = KeyPair::random();
        FriendBot::fundTestAccount($keyPairUser->getAccountId());
        $user = new User();
        $user->setEnabled(true);
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setAddress($keyPairUser->getAccountId());
        $user->setSecret($keyPairUser->getSecretSeed());

        $this->em->persist($user);
        $this->em->flush();

        $output->writeln('User created: ' . $user->getAddress());

        $this->interactManager->mintUserWithToken($id, $user, 50000000);
        $output->writeln('User minted with 50000000 tokens');

        return Command::SUCCESS;
    }
}