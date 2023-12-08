<?php

namespace App\Repository;

use App\Entity\Contract;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Contract>
 *
 * @method Contract|null find($id, $lockMode = null, $lockVersion = null)
 * @method Contract|null findOneBy(array $criteria, array $orderBy = null)
 * @method Contract[]    findAll()
 * @method Contract[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContractRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contract::class);
    }


    public function findByUserAsReceiver(User $receiver): array
    {
        $qb = $this->createQueryBuilder('c');
        return $qb
            ->andWhere($qb->expr()->eq('c.receiver', ':receiver'))
            ->setParameter('receiver', $receiver)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByUserAsSender(User $sender): array
    {
        $qb = $this->createQueryBuilder('c');
        return $qb
            ->andWhere($qb->expr()->eq('c.sender', ':sender'))
            ->setParameter('sender', $sender)
            ->getQuery()
            ->getResult()
        ;
    }
}
