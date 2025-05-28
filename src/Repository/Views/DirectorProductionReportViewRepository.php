<?php

namespace App\Repository\Views;

use App\Entity\Views\DirectorProductionReportView;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DirectorProductionReportView>
 */
class DirectorProductionReportViewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DirectorProductionReportView::class);
    }
}
