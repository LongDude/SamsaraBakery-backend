<?php

namespace App\Controller\Api;

use App\Entity\Affiliates;
use App\Entity\Products;
use App\Repository\Views\DirectorAffiliatesFinanceViewRepository;
use App\Repository\ProductsRepository;
use App\Repository\Views\DirectorProductionReportSummaryViewRepository;
use App\Repository\Views\DirectorProductionReportViewRepository;
use App\Services\DirectorProductionReportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/director_production_report', name: 'api_director_production_report_')]
class DirectorProductionReportController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request, DirectorProductionReportViewRepository $directorProductionReportViewRepository, DirectorProductionReportSummaryViewRepository $directorProductionReportSummaryViewRepository): JsonResponse
    {
        $limit = $request->query->getInt('limit', 10);
        $offset = $request->query->getInt('offset', 1);
        $sort = $request->query->get('sort', '');   
        $order = $request->query->get('order', '');
        $search = $request->query->get('search', '');
        $dateFrom = $request->query->get('date_from', '');
        $dateTo = $request->query->get('date_to', '');
        $summary = $request->query->get('summary', false);
        $summary = $summary == 'true';
        
        if ($summary) {
            $qb = $directorProductionReportSummaryViewRepository->createQueryBuilder('d');
        } else {
            $qb = $directorProductionReportViewRepository->createQueryBuilder('d');
        }

        if ($search) {
            $qb->where('d.product_name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        if (!$summary) {
            if ($dateFrom && $dateTo) {
                $qb->andWhere('d.date BETWEEN :date_from AND :date_to')
                ->setParameter('date_from', $dateFrom)
                ->setParameter('date_to', $dateTo);
            } 
            elseif ($dateFrom) {
                $qb->andWhere('d.date >= :date_from')
                ->setParameter('date_from', $dateFrom);
            }
            elseif ($dateTo) {
                $qb->andWhere('d.date <= :date_to')
                ->setParameter('date_to', $dateTo);
            }
            if ($sort === '') { $sort = 'date'; }
            if ($order === '') { $order = 'desc'; }
        } else {
            if ($sort === '') { $sort = 'product_name'; }
            if ($order === '') { $order = 'asc'; }
        }
        
        $countQb = clone $qb;
        $countQb->select('COUNT(d.product_name)');
        $totalProducts = $countQb->getQuery()->getSingleScalarResult();
        
        $qb->orderBy('d.' . $sort, $order);
        $data = $qb
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();


        $resp_data = array_map(function($product) use ($summary) {
            $row = [
                'product_name' => $product->getProductName(),
                'sells_revenue' => $product->getSellsRevenue(),
                'orders_revenue' => $product->getOrdersRevenue(),
                'production_cost' => $product->getProductionCost(),
                'producted_count' => $product->getProductedCount(),
                'sold_count' => $product->getSoldCount(),
                'ordered_count' => $product->getOrderedCount(),
                'realisation_index' => $product->getRealisationIndex(),
                'order_index' => $product->getOrderIndex(),
                'net_revenue' => $product->getNetRevenue(),
            ];
            if (!$summary) {
                $row['date'] = $product->getDate()?->format('d-m-Y');
            }
            return $row;
        }, $data);

        return $this->json([
            'content' => $resp_data,
            'totalElements' => $totalProducts,
        ]);
    }

    #[Route('/report/excel', name: 'report_excel', methods: ['GET'])]
    public function reportExcel(Request $request, DirectorProductionReportViewRepository $directorProductionReportViewRepository, DirectorProductionReportSummaryViewRepository $directorProductionReportSummaryViewRepository): BinaryFileResponse
    {
        $sort = $request->query->get('sort', '');   
        $order = $request->query->get('order', '');
        $search = $request->query->get('search', '');
        $dateFrom = $request->query->get('date_from', '');
        $dateTo = $request->query->get('date_to', '');
        $summary = $request->query->get('summary', false);
        $summary = $summary == 'true';
        
        if ($summary) {
            $qb = $directorProductionReportSummaryViewRepository->createQueryBuilder('d');
        } else {
            $qb = $directorProductionReportViewRepository->createQueryBuilder('d');
        }

        if ($search) {
            $qb->where('d.product_name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        if (!$summary) {
            if ($dateFrom && $dateTo) {
                $qb->andWhere('d.date BETWEEN :date_from AND :date_to')
                ->setParameter('date_from', $dateFrom)
                ->setParameter('date_to', $dateTo);
            } 
            elseif ($dateFrom) {
                $qb->andWhere('d.date >= :date_from')
                ->setParameter('date_from', $dateFrom);
            }
            elseif ($dateTo) {
                $qb->andWhere('d.date <= :date_to')
                ->setParameter('date_to', $dateTo);
            }
            if ($sort === '') { $sort = 'date'; }
            if ($order === '') { $order = 'desc'; }
        } else {
            if ($sort === '') { $sort = 'product_name'; }
            if ($order === '') { $order = 'asc'; }
        }
        
        $qb->orderBy('d.' . $sort, $order);
        $data = $qb
            ->getQuery()
            ->getResult();

        $resp_data = array_map(function($product) use ($summary) {
            $row = [
                'product_name' => $product->getProductName(),
                'sells_revenue' => $product->getSellsRevenue(),
                'orders_revenue' => $product->getOrdersRevenue(),
                'production_cost' => $product->getProductionCost(),
                'producted_count' => $product->getProductedCount(),
                'sold_count' => $product->getSoldCount(),
                'ordered_count' => $product->getOrderedCount(),
                'realisation_index' => $product->getRealisationIndex(),
                'order_index' => $product->getOrderIndex(),
                'net_revenue' => $product->getNetRevenue(),
            ];
            if (!$summary) {
                $row['date'] = $product->getDate()?->format('d-m-Y');
            }
            return $row;
        }, $data);


        return DirectorProductionReportService::generateExcel($resp_data, $summary);
    }

    #[Route('/report/pdf', name: 'report_pdf', methods: ['GET'])]
    public function reportPdf(Request $request, DirectorProductionReportViewRepository $directorProductionReportViewRepository, DirectorProductionReportSummaryViewRepository $directorProductionReportSummaryViewRepository): Response
    {   
        $sort = $request->query->get('sort', '');   
        $order = $request->query->get('order', '');
        $search = $request->query->get('search', '');
        $dateFrom = $request->query->get('date_from', '');
        $dateTo = $request->query->get('date_to', '');
        $summary = $request->query->get('summary', false);
        $summary = $summary == 'true';
        
        if ($summary) {
            $qb = $directorProductionReportSummaryViewRepository->createQueryBuilder('d');
        } else {
            $qb = $directorProductionReportViewRepository->createQueryBuilder('d');
        }

        if ($search) {
            $qb->where('d.product_name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        if (!$summary) {
            if ($dateFrom && $dateTo) {
                $qb->andWhere('d.date BETWEEN :date_from AND :date_to')
                ->setParameter('date_from', $dateFrom)
                ->setParameter('date_to', $dateTo);
            } 
            elseif ($dateFrom) {
                $qb->andWhere('d.date >= :date_from')
                ->setParameter('date_from', $dateFrom);
            }
            elseif ($dateTo) {
                $qb->andWhere('d.date <= :date_to')
                ->setParameter('date_to', $dateTo);
            }
            if ($sort === '') { $sort = 'date'; }
            if ($order === '') { $order = 'desc'; }
        } else {
            if ($sort === '') { $sort = 'product_name'; }
            if ($order === '') { $order = 'asc'; }
        }
        
        $qb->orderBy('d.' . $sort, $order);
        $data = $qb
            ->getQuery()
            ->getResult();

        $resp_data = array_map(function($product) use ($summary) {
            $row = [
                'product_name' => $product->getProductName(),
                'sells_revenue' => $product->getSellsRevenue(),
                'orders_revenue' => $product->getOrdersRevenue(),
                'production_cost' => $product->getProductionCost(),
                'producted_count' => $product->getProductedCount(),
                'sold_count' => $product->getSoldCount(),
                'ordered_count' => $product->getOrderedCount(),
                'realisation_index' => $product->getRealisationIndex(),
                'order_index' => $product->getOrderIndex(),
                'net_revenue' => $product->getNetRevenue(),
            ];
            if (!$summary) {
                $row['date'] = $product->getDate()?->format('d-m-Y');
            }
            return $row;
        }, $data);
        
        return DirectorProductionReportService::generatePdf($resp_data, $summary);
    }
}