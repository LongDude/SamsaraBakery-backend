<?php

namespace App\Repository;

use App\Entity\Affiliates;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Affiliates>
 */
class AffiliatesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Affiliates::class);
    }

    public function listAll(){
        return $this->createQueryBuilder("a")
        ->join("a.manager_id", "m")
        ->getQuery()
        ->getResult();
    }


    public function listRevenue(
        DateTimeImmutable $timeFrom,
        DateTimeImmutable $timeUntil,
    ) {
        $rep = new ProductsMovementRepository($this->getEntityManager());
        $rep->select("")
        ->join("a.manager_id", "m")
        ->join();
    }
}
