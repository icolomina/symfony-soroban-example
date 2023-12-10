<?php

namespace App\Controller;

use App\Contract\ContractManager;
use App\Entity\Contract;
use App\Request\Input\CreateContractInput;
use App\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Soneso\StellarSDK\Crypto\StrKey;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/panel')]
class PanelController extends AbstractController
{
    #[Route('/index', name: 'panel_index', methods: ['GET'])]
    public function getPanel(UserManager $userManager): Response
    {
        $users = $userManager->getUsers($this->getUser());
        return $this->render('panel/panel.html.twig', ['users' => $users]);
    }

    #[Route('/user/address', name: 'panel_user_post_address', methods: ['POST'])]
    public function postUserAddress(Request $request, UserManager $userManager): JsonResponse
    {
        $body = json_decode($request->getContent(), true);
        $userManager->setUserAddress($this->getUser(), $body['address']);
        return new JsonResponse(null, 204);
    }

    #[Route('/user/contract-create', name: 'panel_user_get_contract_form', methods: ['GET'])]
    public function getCreateContract(): Response
    {
        return $this->render('panel/create_contract.html.twig');
    }

    #[Route('/user/contract', name: 'panel_user_post_contract', methods: ['POST'])]
    public function postContract(Request $request, SerializerInterface $serializer, ContractManager $contractManager): JsonResponse
    {
        $createContractInput = $serializer->deserialize($request->getContent(), CreateContractInput::class, 'json');
        $contract = $contractManager->createContract($createContractInput, $this->getUser());

        return new JsonResponse(['id' => $contract->getId()], Response::HTTP_CREATED);
    }

    #[Route('/user/contracts', name: 'panel_user_get_contracts', methods: ['GET'])]
    public function getContracts(ContractManager $contractManager): Response
    {
        $contracts = $contractManager->getContracts();
        return $this->render('panel/contracts.html.twig', [ 'contracts' => $contracts ]);
    }

    #[Route('/user/deposit-create', name: 'panel_user_get_deposit_form', methods: ['GET'])]
    public function getSendDeposit(EntityManagerInterface $em): Response
    {
        $contract = $em->getRepository(Contract::class)->findOneBy(['sender' => $this->getUser()]);
        return $this->render('panel/create_deposit.html.twig', ['contract' => StrKey::encodeContractIdHex($contract->getAddress())]);
    }
}
