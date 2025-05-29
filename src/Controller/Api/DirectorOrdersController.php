<?php

namespace App\Controller\Api;

use App\Enum\OrderStatus;
use App\Entity\Orders;
use App\Entity\Products;
use App\Repository\PartnersRepository;
use App\Repository\ProductsRepository;
use App\Repository\Views\DirectorOrdersViewRepository;
use App\Services\DirectorOrdersViewReportService;
use Doctrine\ORM\EntityManagerInterface;
use Proxies\__CG__\App\Entity\Partners;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/director_orders', name: 'api_director_orders_')]
#[IsGranted('ROLE_DIRECTOR')]
class DirectorOrdersController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request, DirectorOrdersViewRepository $ordersRepository): JsonResponse
    {
        $limit = $request->query->getInt('limit', 10);
        $offset = $request->query->getInt('offset', 1);
        $sort = $request->query->get('sort', 'order_id');
        $order = $request->query->get('order', 'asc');
        $search = $request->query->get('search', '');
        $status = $request->query->get('status', '');
        $dateFrom = $request->query->get('date_from', '');
        $dateTo = $request->query->get('date_to', '');

        $qb = $ordersRepository->createQueryBuilder('o');
        if ($search) {
            $qb->where('o.partner_firmname LIKE :search')
                ->orWhere('o.product LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }
        if ($status) {
            $qb->andWhere('o.status = :status')
               ->setParameter('status', $status);
        }
        if ($dateFrom && $dateTo) {
            $qb->andWhere('o.date BETWEEN :date_from AND :date_to')
               ->setParameter('date_from', $dateFrom)
               ->setParameter('date_to', $dateTo);
        } else if ($dateFrom) {
            $qb->andWhere('o.date >= :date_from')
               ->setParameter('date_from', $dateFrom);
        } else if ($dateTo) {
            $qb->andWhere('o.date <= :date_to')
               ->setParameter('date_to', $dateTo);
        }

        $counterQb = clone $qb;
        $totalOrders = $counterQb
            ->select('COUNT(o.order_id)')
            ->getQuery()
            ->getSingleScalarResult();

        $qb->orderBy('o.' . $sort, $order)
           ->setMaxResults($limit)
           ->setFirstResult($offset);
        $orders = $qb->getQuery()->getResult();

        $data = array_map(function($order) {
            return [
                'order_id' => $order->getOrderId(),
                'partner_firmname' => $order->getPartnerFirmname(),
                'product' => $order->getProduct(),
                'price' => $order->getPrice(),
                'quantity' => $order->getQuantity(),
                'status' => $order->getStatus(),
                'date' => $order->getDate()->format('d-m-Y'),
            ];
        }, $orders);

        return $this->json([
            'content' => $data,
            'totalElements' => $totalOrders,
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $order = new Orders();

        $order->setRecieverPartner($entityManager->getRepository(Partners::class)->find($data['partner_id']));
        $order->setProduct($entityManager->getRepository(Products::class)->find($data['product_id']));
        $order->setPrice($data['price']);
        $order->setQuantity($data['quantity']);
        $order->setStatus(OrderStatus::from($data['status']));
        $order->setDate(new \DateTime($data['date']));

        $entityManager->persist($order);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'id' => $order->getId()
        ]);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request, DirectorOrdersViewRepository $ordersViewRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $order = $entityManager->getRepository(Orders::class)->find($id);
        if (!$order) {
            return $this->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['partner_id'])) {
            $order->setRecieverPartner($entityManager->getRepository(Partners::class)->find($data['partner_id']));
        }   
        if (isset($data['product_id'])) {
            $order->setProduct($entityManager->getRepository(Products::class)->find($data['product_id']));
        }
        if (isset($data['price'])) {
            $order->setPrice($data['price']);
        }
        if (isset($data['quantity'])) {
            $order->setQuantity($data['quantity']);
        }
        if (isset($data['status'])) {
            $order->setStatus(OrderStatus::from($data['status']));
        }
        if (isset($data['date'])) {
            $order->setDate(new \DateTime($data['date']));
        }
        $entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, DirectorOrdersViewRepository $ordersViewRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $order = $ordersViewRepository->find($id);
        if (!$order) {
            return $this->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $entityManager->remove($order);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/report/excel', name: 'report_excel', methods: ['GET'])]
    public function reportExcel(Request $request, DirectorOrdersViewRepository $ordersViewRepository): Response
    {
        $sort = $request->query->get('sort', 'order_id');
        $order = $request->query->get('order', 'asc');
        $search = $request->query->get('search', '');
        $status = $request->query->get('status', '');
        $dateFrom = $request->query->get('date_from', '');
        $dateTo = $request->query->get('date_to', '');

        $qb = $ordersViewRepository->createQueryBuilder('o');
        if ($search) {
            $qb->where('o.partner_firmname LIKE :search')
                ->orWhere('o.product LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }
        if ($status) {
            $qb->andWhere('o.status = :status')
               ->setParameter('status', $status);
        }
        if ($dateFrom && $dateTo) {
            $qb->andWhere('o.date BETWEEN :date_from AND :date_to')
               ->setParameter('date_from', $dateFrom)
               ->setParameter('date_to', $dateTo);
        } else if ($dateFrom) {
            $qb->andWhere('o.date >= :date_from')
               ->setParameter('date_from', $dateFrom);
        } else if ($dateTo) {
            $qb->andWhere('o.date <= :date_to')
               ->setParameter('date_to', $dateTo);
        }
        $orders = $qb->orderBy('o.' . $sort, $order)
           ->getQuery()->getResult();

        $data = array_map(function($order) {
        return [
            'order_id' => $order->getOrderId(),
            'partner_firmname' => $order->getPartnerFirmname(),
            'product' => $order->getProduct(),
            'price' => $order->getPrice(),
            'quantity' => $order->getQuantity(),
            'status' => $order->getStatus(),
            'date' => $order->getDate()->format('d-m-Y'),
        ];
        }, $orders);

           $excelContent = DirectorOrdersViewReportService::generateExcel($data);

        return $excelContent;
    }

    #[Route('/report/pdf', name: 'report_pdf', methods: ['GET'])]
    public function reportPdf(Request $request, DirectorOrdersViewRepository $ordersViewRepository): Response
    {
        $sort = $request->query->get('sort', 'order_id');
        $order = $request->query->get('order', 'asc');
        $search = $request->query->get('search', '');
        $status = $request->query->get('status', '');
        $dateFrom = $request->query->get('date_from', '');
        $dateTo = $request->query->get('date_to', '');

        $qb = $ordersViewRepository->createQueryBuilder('o');

        if ($sort === '') $sort = 'order_id';
        if ($order === '') $order = 'asc';

        if ($search) {
            $qb->where('o.partner_firmname LIKE :search')
                ->orWhere('o.product LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }
        if ($status) {
            $qb->andWhere('o.status = :status')
               ->setParameter('status', $status);
        }
        if ($dateFrom && $dateTo) {
            $qb->andWhere('o.date BETWEEN :date_from AND :date_to')
               ->setParameter('date_from', $dateFrom)
               ->setParameter('date_to', $dateTo);
        } else if ($dateFrom) {
            $qb->andWhere('o.date >= :date_from')
               ->setParameter('date_from', $dateFrom);
        } else if ($dateTo) {
            $qb->andWhere('o.date <= :date_to')
               ->setParameter('date_to', $dateTo);
        }

        $orders = $qb->orderBy('o.' . $sort, $order)
            ->getQuery()->getResult();

        $data = array_map(function($order) {
        return [
            'order_id' => $order->getOrderId(),
            'partner_firmname' => $order->getPartnerFirmname(),
            'product' => $order->getProduct(),
            'price' => $order->getPrice(),
            'quantity' => $order->getQuantity(),
            'status' => $order->getStatus(),
            'date' => $order->getDate()->format('d-m-Y'),
        ];
        }, $orders);

        $pdfContent = DirectorOrdersViewReportService::generatePdf($data);
        return $pdfContent;
    }

    #[Route('/search/products', name: 'search_products', methods: ['GET'])]
    public function searchProducts(Request $request, EntityManagerInterface $entityManager, ProductsRepository $productsRepository): JsonResponse
    {
        $search = $request->query->get('q', '');
        $products = $productsRepository->createQueryBuilder('p')
            ->where('p.name LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $data = array_map(function($product) {
            return [
                'label' => $product->getName(),
                'id' => $product->getId(),
            ];
        }, $products);
        return $this->json($data);
    }

    #[Route('/search/partners', name: 'search_partners', methods: ['GET'])]
    public function searchPartners(Request $request, EntityManagerInterface $entityManager, PartnersRepository $partnersRepository): JsonResponse
    {
        $search = $request->query->get('q', '');
        $partners = $partnersRepository->createQueryBuilder('p')
            ->where('p.firmname LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $data = array_map(function($partner) {
            return [
                'label' => $partner->getFirmname(),
                'id' => $partner->getId(),
            ];
        }, $partners);
        return $this->json($data);
    }
} 