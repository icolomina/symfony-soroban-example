<?php 

namespace App\Contract;

use App\Entity\Contract;
use App\Entity\Token;
use App\Entity\User;
use App\Request\Input\CreateContractInput;
use App\Stellar\Soroban\Contract\InstallManager;
use App\Stellar\Soroban\Contract\InteractManager;
use Doctrine\ORM\EntityManagerInterface;

class ContractManager {

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly InstallManager $contractInstallManager,
        private readonly InteractManager $contractInteractManager
    ){}

    public function createContract(CreateContractInput $createContractInput, User $sender): Contract
    {
        $token = $this->em->getRepository(Token::class)->findOneBy(['code' => $createContractInput->getToken()]);
        $receiver = $this->em->getRepository(User::class)->findOneBy(['address' => $createContractInput->getReceiver()]);

        $contract = new Contract();
        $contract->setReceiver($receiver);
        $contract->setToken($token);
        $contract->setCreatedAt(new \DateTimeImmutable());
        $contract->setLabel($createContractInput->getLabel());
        $contract->setDescription($createContractInput->getDescription());
        $contract->setEnabled(true);
        $contract->setSender($sender);

        $contractId = $this->contractInstallManager->installContract();
        $contract->setAddress($contractId);
        $this->contractInteractManager->initContract($contract);

        $this->em->persist($contract);
        $this->em->flush();

        return $contract;

    }

    public function getContracts(): array
    {
        return $this->em->getRepository(Contract::class)->findAll();
    }

    public function getToken(): Token 
    {
        $tokens = $this->em->getRepository(Token::class)->findAll();
        return $tokens[0];
    }
}