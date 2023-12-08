<?php

namespace App\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserManager {

    public function __construct(
        private readonly EntityManagerInterface $em
    ){}

    public function getUsers(User $currentUser): array
    {
        return $this->em->getRepository(User::class)->findUsers($currentUser);
    }

    public function setUserAddress(User $currentUser, string $address): void
    {
        $currentUser->setAddress($address);
        $this->em->persist($currentUser);
        $this->em->flush();
    }
}