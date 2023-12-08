<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ){}

    public function load(ObjectManager $manager): void
    {
        $user1 = new User();
        $user1->setEmail('user1@domain.com');
        $user1->setName('Peter Parker');
        $user1->setEnabled(true);
        $user1->setCreatedAt(new \DateTimeImmutable());
        $user1->setPassword($this->passwordHasher->hashPassword($user1, 'peterparker'));

        $user2 = new User();
        $user2->setEmail('user2@domain.com');
        $user2->setName('Roger Rabbit');
        $user2->setEnabled(true);
        $user2->setCreatedAt(new \DateTimeImmutable());
        $user2->setPassword($this->passwordHasher->hashPassword($user2, 'rrabit'));

        $manager->persist($user1);
        $manager->persist($user2);
        $manager->flush();
    }
}