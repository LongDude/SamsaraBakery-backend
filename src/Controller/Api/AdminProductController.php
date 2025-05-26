<?php

namespace App\Controller\Api;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
#[IsGranted('ROLE_ADMIN')]
class AdminProductController extends AbstractController
{
    #[Route('/admin_view_products', name: 'api_admin_products_index', methods: ['GET'])]
    public function index(
        Request $request,
        ProductsRepository $productsRepository
    ): Response {
        $page = $request->query->getInt('page', 1);
        $search = $request->query->get('search', '');
        $sortField = $request->query->get('sortfield');
        $sortOrder = $request->query->get('sortorder', 'ASC');
        $itemsPerPage = 10;

        // Преобразуем camelCase в snake_case для сортировки
        if ($sortField) {
            $sortField = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $sortField));
        }

        // Создаем базовый запрос для подсчета
        $countQueryBuilder = $productsRepository->createQueryBuilder('p')
            ->select('COUNT(p.id)');

        // Создаем запрос для получения данных
        $queryBuilder = $productsRepository->createQueryBuilder('p');

        if ($search) {
            $countQueryBuilder->andWhere('p.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
            $queryBuilder->andWhere('p.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Получаем общее количество записей
        $totalItems = $countQueryBuilder->getQuery()->getSingleScalarResult();
        $totalPages = ceil($totalItems / $itemsPerPage);

        // Добавляем сортировку только к основному запросу
        if ($sortField) {
            $queryBuilder->orderBy('p.' . $sortField, $sortOrder);
        }

        // Устанавливаем ограничения для текущей страницы
        $queryBuilder->setFirstResult(($page - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage);

        $products = $queryBuilder->getQuery()->getResult();

        return $this->render('products/_admin_table.html.twig', [
            'products' => $products,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_items' => $totalItems,
            'items_per_page' => $itemsPerPage
        ]);
    }

    #[Route('/admin_view_products/search', name: 'api_products_search', methods: ['GET'])]
    public function search(
        Request $request,
        ProductsRepository $productsRepository
    ): JsonResponse {
        $query = $request->query->get('q', '');
        
        if (empty($query)) {
            return $this->json(['results' => []]);
        }

        $products = $productsRepository->createQueryBuilder('p')
            ->where('p.name LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $results = array_map(function(Products $product) {
            return [
                'id' => $product->getId(),
                'name' => $product->getName()
            ];
        }, $products);

        return $this->json(['results' => $results]);
    }

    #[Route('/admin_view_products/{id}/edit', name: 'api_admin_view_products_edit', methods: ['POST'])]
    public function edit(
        Request $request,
        Products $product,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['field']) || !isset($data['value'])) {
            return $this->json(['success' => false, 'message' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
        }

        $field = $data['field'];
        $value = $data['value'];

        // Validate field name
        if (!in_array($field, ['name', 'quantityStoraged', 'productionCost'])) {
            return $this->json(['success' => false, 'message' => 'Invalid field'], Response::HTTP_BAD_REQUEST);
        }

        // Validate value based on field type
        if ($field === 'quantityStoraged' || $field === 'productionCost') {
            if (!is_numeric($value) || $value < 0) {
                return $this->json(['success' => false, 'message' => 'Invalid value'], Response::HTTP_BAD_REQUEST);
            }
        }

        // Update the field
        if ($field === 'name') $product->setName($value);
        if ($field === 'quantityStoraged') $product->setQuantityStoraged($value);
        if ($field === 'productionCost') $product->setProductionCost($value);

        try {
            $entityManager->flush();
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Database error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/admin_view_products', name: 'api_products_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || !isset($data['quantityStoraged']) || !isset($data['productionCost'])) {
            return $this->json(['success' => false, 'message' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        // Validate data
        if (!is_numeric($data['quantityStoraged']) || $data['quantityStoraged'] < 0) {
            return $this->json(['success' => false, 'message' => 'Invalid quantity'], Response::HTTP_BAD_REQUEST);
        }

        if (!is_numeric($data['productionCost']) || $data['productionCost'] < 0) {
            return $this->json(['success' => false, 'message' => 'Invalid production cost'], Response::HTTP_BAD_REQUEST);
        }

        $product = new Products();
        $product->setName($data['name']);
        $product->setQuantityStoraged($data['quantityStoraged']);
        $product->setProductionCost($data['productionCost']);

        try {
            $entityManager->persist($product);
            $entityManager->flush();
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Database error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/admin_view_products/{id}', name: 'api_products_delete', methods: ['DELETE'])]
    public function delete(
        Products $product,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        try {
            $entityManager->remove($product);
            $entityManager->flush();
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Database error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 