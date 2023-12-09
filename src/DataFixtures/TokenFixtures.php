<?php

namespace App\DataFixtures;

use App\Entity\Token;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TokenFixtures extends Fixture
{

    public function __construct(
        private readonly string $sorobanTokenAddr
    ){}

    public function load(ObjectManager $manager): void
    {
        $token = new Token();
        $token->setName('MyToken');
        $token->setCode('MTI');
        $token->setAddress($this->sorobanTokenAddr);
        $token->setEnabled(true);
        $token->setCreatedAt(new \DateTimeImmutable());

        $manager->persist($token);
        $manager->flush();
    }
}