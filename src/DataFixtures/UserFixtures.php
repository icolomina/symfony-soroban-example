<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Soneso\StellarSDK\Crypto\KeyPair;
use Soneso\StellarSDK\Util\FriendBot;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ){}

    public function load(ObjectManager $manager): void
    {
        /*$keyPair1 = KeyPair::random();
        FriendBot::fundTestAccount($keyPair1->getAccountId());

        $user1 = new User();
        $user1->setEnabled(true);
        $user1->setCreatedAt(new \DateTimeImmutable());
        $user1->setAddress($keyPair1->getPublicKey());
        $user1->setSecret($keyPair1->getSecretSeed());

        $manager->persist($user1);
        $manager->flush();*/
    }
}
