<?php

namespace App\DataFixtures;

use App\Entity\Token;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TokenFixtures extends Fixture
{

    public function load(ObjectManager $manager): void
    {
        $token = new Token();
        $token->setName('MyToken');
        $token->setCode('MTI');
        $token->setAddress('4379gvbg439tvb48t0vbt8b58tyb58by580tb5');
        $token->setEnabled(true);
        $token->setCreatedAt(new \DateTimeImmutable());

        $manager->persist($token);
        $manager->flush();
    }
}