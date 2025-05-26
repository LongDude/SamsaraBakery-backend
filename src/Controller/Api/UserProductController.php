<?php

namespace App\Controller\Api;

use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
#[IsGranted('ROLE_USER')]
class UserProductController extends AbstractController
{
    #[Route('/user_view_products', name: 'api_user_products_index', methods: ['GET'])]
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

        return $this->render('products/_user_table.html.twig', [
            'products' => $products,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_items' => $totalItems,
            'items_per_page' => $itemsPerPage
        ]);
    }

    #[Route('/user_view_products/search', name: 'api_user_products_search', methods: ['GET'])]
    public function search(
        Request $request,
        ProductsRepository $productsRepository
    ): Response {
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

        $results = array_map(function($product) {
            return [
                'id' => $product->getId(),
                'name' => $product->getName()
            ];
        }, $products);

        return $this->json(['results' => $results]);
    }
} 