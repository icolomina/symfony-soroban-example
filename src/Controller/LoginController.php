<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class LoginController extends AbstractController
{

    #[Route(path: '/login', name: 'app_get_login', methods: ['GET'])]
    public function getLogin(#[CurrentUser] ?User $user): Response
    {
        return $this->render('security/login.html.twig');
    }
    
    #[Route(path: '/login', name: 'app_post_login', methods: ['POST'])]
    public function postLogin(#[CurrentUser] ?User $user): Response
    {
        if (null === $user) {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

    
        return $this->json([
            'url' => $this->generateUrl('panel_index', [], UrlGeneratorInterface::ABSOLUTE_URL)
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
