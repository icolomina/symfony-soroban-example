<?php

namespace App\Security;

use App\Entity\Token;
use App\Entity\User;
use App\Stellar\Soroban\Contract\InteractManager;
use Doctrine\ORM\EntityManagerInterface;
use Soneso\StellarSDK\Crypto\KeyPair;
use Soneso\StellarSDK\Util\FriendBot;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\InteractiveAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class WalletAuthenticator implements InteractiveAuthenticatorInterface {

    public const LOGIN_ROUTE = 'app_get_login';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator, 
        private readonly EntityManagerInterface $em,
        private readonly InteractManager $interactManager
    ){}

    public function supports(Request $request): ?bool
    {
        return $request->isMethod('POST') && $this->getLoginUrl($request) === $request->getBaseUrl().$request->getPathInfo();
    }

    public function authenticate(Request $request): Passport
    {
        $jsonContent = json_decode($request->getContent(), true);
        if(!isset($jsonContent['address'])) {
            throw new CustomUserMessageAuthenticationException('Missing credentials');
        }

        $address = $jsonContent['address'];
        $user = $this->em->getRepository(User::class)->findOneBy(['address' => $address]);
        if(!$user) {

            $keyPair = KeyPair::random();
            FriendBot::fundTestAccount($keyPair->getAccountId());
            
            $user = new User();
            $user->setEnabled(true);
            $user->setAddress($address);
            $user->setSecret($keyPair->getSecretSeed());
            $user->setCreatedAt(new \DateTimeImmutable());
            $this->em->persist($user);
            $this->em->flush();

            // we have to mint user if we create it 
            $token = $this->em->getRepository(Token::class)->findOneBy(['name' => 'MyToken']);
            $this->interactManager->mintUserWithToken($token->getAddress(), $user, 5000000);

        }

        return new SelfValidatingPassport(new UserBadge($address));
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        return new UsernamePasswordToken($passport->getUser(), $firewallName, $passport->getUser()->getRoles());
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(['error' => $exception->getMessage()], JsonResponse::HTTP_UNAUTHORIZED);
    }

    public function isInteractive(): bool
    {
        return true;
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}