<?php

namespace App\Controller;

use App\Contract\ContractManager;
use App\Entity\Contract;
use App\Request\Input\CreateContractInput;
use App\Stellar\Soroban\Contract\InteractManager;
use App\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Soneso\StellarSDK\Crypto\StrKey;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class PanelController extends AbstractController
{

    #[Route('/', name: 'get_contracts', methods: ['GET'])]
    public function getContracts(ContractManager $contractManager): Response
    {
        $contracts = $contractManager->getContracts();
        return $this->render('panel/contracts.html.twig', [ 'contracts' => $contracts ]);
    }

    #[Route('/contract-new', name: 'get_new_contract', methods: ['GET'])]
    public function getCreateContract(ContractManager $contractManager): Response
    {
        $token = $contractManager->getToken();
        return $this->render('panel/create_contract.html.twig', ['token' => $token]);
    }

    #[Route('/contract-create', name: 'post_create_contract', methods: ['POST'])]
    public function postContract(Request $request, SerializerInterface $serializer, ContractManager $contractManager): JsonResponse
    {
        $createContractInput = $serializer->deserialize($request->getContent(), CreateContractInput::class, 'json');
        $contract = $contractManager->createContract($createContractInput, $this->getUser());

        return new JsonResponse(['id' => $contract->getId()], Response::HTTP_CREATED);
    }

    #[Route('/contract/{id}/deposit-new', name: 'get_new_deposit', methods: ['GET'])]
    public function getSendDeposit(Contract $contract): Response
    {
        return $this->render('panel/create_deposit.html.twig', ['contract' => $contract ]);
    }

    #[Route('/contract/{id}/deposit-create', name: 'post_create_deposit', methods: ['POST'])]
    public function postSendDeposit(Contract $contract, Request $request, InteractManager $interactManager): JsonResponse
    {
        $body = json_decode($request->getContent(), true);
        $balance  = $interactManager->depositInContract($contract, (int)$body['amount']);

        return new JsonResponse(['balance' => $balance]);
    }
}
