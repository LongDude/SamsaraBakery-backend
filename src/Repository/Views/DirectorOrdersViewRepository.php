<?php

namespace App\Repository\Views;

use App\Entity\Views\DirectorOrdersView;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DirectorOrdersView>
 */
class DirectorOrdersViewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DirectorOrdersView::class);
    }
}
