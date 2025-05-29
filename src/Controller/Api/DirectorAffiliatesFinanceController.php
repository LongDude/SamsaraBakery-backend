<?php

namespace App\Controller\Api;

use App\Entity\Affiliates;
use App\Entity\Products;
use App\Repository\Views\DirectorAffiliatesFinanceViewRepository;
use App\Repository\ProductsRepository;
use App\Services\DirectorAffiliatesFinanceReportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/director_affiliates_finance', name: 'api_director_affiliates_finance_')]
class DirectorAffiliatesFinanceController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request, DirectorAffiliatesFinanceViewRepository $directorAffiliatesFinanceViewRepository): JsonResponse
    {
        $limit = $request->query->getInt('limit', 10);
        $offset = $request->query->getInt('offset', 1);
        $sort = $request->query->get('sort', 'day');
        $order = $request->query->get('order', 'desc');
        $search = $request->query->get('search', '');
        $dateFrom = $request->query->get('date_from', '');
        $dateTo = $request->query->get('date_to', '');

        $qb = $directorAffiliatesFinanceViewRepository->createQueryBuilder('d');
        if ($search) {
            $qb->where('d.affiliate_address LIKE :search')
                ->orWhere('d.contact_number LIKE :search')
                ->orWhere('d.manager_name LIKE :search')
                ->orWhere('d.manager_phone LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        if ($dateFrom && $dateTo) {
            $qb->andWhere('d.day BETWEEN :date_from AND :date_to')
                ->setParameter('date_from', $dateFrom)
                ->setParameter('date_to', $dateTo);
        } 
        elseif ($dateFrom) {
            $qb->andWhere('d.day >= :date_from')
                ->setParameter('date_from', $dateFrom);
        }
        elseif ($dateTo) {
            $qb->andWhere('d.day <= :date_to')
                ->setParameter('date_to', $dateTo);
        }
        if ($sort === '') { $sort = 'day'; }
        if ($order === '') { $order = 'desc'; }
        
        $countQb = clone $qb;
        $countQb->select('COUNT(d.affiliate_id)');
        $totalAffiliates = $countQb->getQuery()->getSingleScalarResult();
        
        $data = $qb
            ->orderBy('d.' . $sort, $order)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();


        $resp_data = array_map(function($affiliate) {
            return [
                'affiliate_id' => $affiliate->getAffiliateId(),
                'affiliate_address' => $affiliate->getAffiliateAddress(),
                'contact_number' => $affiliate->getContactNumber(),
                'manager_name' => $affiliate->getManagerName(),
                'manager_phone' => $affiliate->getManagerPhone(),
                'day' => $affiliate->getDay()->format('d-m-Y'),
                'revenue' => $affiliate->getRevenue(),
                'cost' => $affiliate->getCost(),
                'net_revenue' => $affiliate->getNetRevenue(),
            ];
        }, $data);

        return $this->json([
            'content' => $resp_data,
            'totalElements' => $totalAffiliates,
        ]);
    }

    #[Route('/report/excel', name: 'report_excel', methods: ['GET'])]
    public function reportExcel(Request $request, DirectorAffiliatesFinanceViewRepository $directorAffiliatesFinanceViewRepository): BinaryFileResponse
    {
        $sort = $request->query->get('sort', 'day');
        $order = $request->query->get('order', 'desc');
        $search = $request->query->get('search', '');
        $dateFrom = $request->query->get('date_from', '');
        $dateTo = $request->query->get('date_to', '');

        $qb = $directorAffiliatesFinanceViewRepository->createQueryBuilder('d');
        if ($search) {
            $qb->where('d.affiliate_address LIKE :search')
                ->orWhere('d.contact_number LIKE :search')
                ->orWhere('d.manager_name LIKE :search')
                ->orWhere('d.manager_phone LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        if ($dateFrom && $dateTo) {
            $qb->andWhere('d.day BETWEEN :date_from AND :date_to')
                ->setParameter('date_from', $dateFrom)
                ->setParameter('date_to', $dateTo);
        } 
        elseif ($dateFrom) {
            $qb->andWhere('d.day >= :date_from')
                ->setParameter('date_from', $dateFrom);
        }
        elseif ($dateTo) {
            $qb->andWhere('d.day <= :date_to')
                ->setParameter('date_to', $dateTo);
        }
        if ($sort === '') { $sort = 'day'; }
        if ($order === '') { $order = 'desc'; }

        $qb->orderBy('d.' . $sort, $order);
        $data = $qb
            ->getQuery()
            ->getResult();


        $resp_data = array_map(function($affiliate) {
            return [
                'affiliate_id' => $affiliate->getAffiliateId(),
                'affiliate_address' => $affiliate->getAffiliateAddress(),
                'contact_number' => $affiliate->getContactNumber(),
                'manager_name' => $affiliate->getManagerName(),
                'manager_phone' => $affiliate->getManagerPhone(),
                'day' => $affiliate->getDay()->format('d-m-Y'),
                'revenue' => $affiliate->getRevenue(),
                'cost' => $affiliate->getCost(),
                'net_revenue' => $affiliate->getNetRevenue(),
            ];
        }, $data);

        return DirectorAffiliatesFinanceReportService::generateExcel($resp_data);
    }

    #[Route('/report/pdf', name: 'report_pdf', methods: ['GET'])]
    public function reportPdf(Request $request, DirectorAffiliatesFinanceViewRepository $directorAffiliatesFinanceViewRepository): Response
    {
        $sort = $request->query->get('sort', 'day');
        $order = $request->query->get('order', 'desc');
        $search = $request->query->get('search', '');
        $dateFrom = $request->query->get('date_from', '');
        $dateTo = $request->query->get('date_to', '');

        $qb = $directorAffiliatesFinanceViewRepository->createQueryBuilder('d');
        if ($search) {
            $qb->where('d.affiliate_address LIKE :search')
                ->orWhere('d.contact_number LIKE :search')
                ->orWhere('d.manager_name LIKE :search')
                ->orWhere('d.manager_phone LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        if ($dateFrom && $dateTo) {
            $qb->andWhere('d.day BETWEEN :date_from AND :date_to')
                ->setParameter('date_from', $dateFrom)
                ->setParameter('date_to', $dateTo);
        } 
        elseif ($dateFrom) {
            $qb->andWhere('d.day >= :date_from')
                ->setParameter('date_from', $dateFrom);
        }
        elseif ($dateTo) {
            $qb->andWhere('d.day <= :date_to')
                ->setParameter('date_to', $dateTo);
        }
        if ($sort === '') { $sort = 'day'; }
        if ($order === '') { $order = 'desc'; }

        $qb->orderBy('d.' . $sort, $order);
        $data = $qb
            ->getQuery()
            ->getResult();


        $resp_data = array_map(function($affiliate) {
            return [
                'affiliate_id' => $affiliate->getAffiliateId(),
                'affiliate_address' => $affiliate->getAffiliateAddress(),
                'contact_number' => $affiliate->getContactNumber(),
                'manager_name' => $affiliate->getManagerName(),
                'manager_phone' => $affiliate->getManagerPhone(),
                'day' => $affiliate->getDay()->format('d-m-Y'),
                'revenue' => $affiliate->getRevenue(),
                'cost' => $affiliate->getCost(),
                'net_revenue' => $affiliate->getNetRevenue(),
            ];
        }, $data);
        
        return DirectorAffiliatesFinanceReportService::generatePdf($resp_data);
    }
}