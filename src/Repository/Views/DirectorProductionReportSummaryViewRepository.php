<?php

namespace App\Repository\Views;

use App\Entity\Views\DirectorProductionReportSummaryView;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DirectorProductionReportSummaryView>
 */
class DirectorProductionReportSummaryViewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DirectorProductionReportSummaryView::class);
    }
}
