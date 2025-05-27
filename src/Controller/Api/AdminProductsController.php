<?php

namespace App\Controller\Api;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin_view_products')]
class AdminProductsController extends AbstractController
{
    #[Route('', name: 'api_products_list', methods: ['GET'])]
    public function list(Request $request, ProductsRepository $productsRepository): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $sort = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'asc');

        $products = $productsRepository->findBy([], [$sort => $order], $limit, $offset);
        $totalProducts = $productsRepository->count([]);
        $totalPages = ceil($totalProducts / $limit);

        $data = array_map(function($product) {
            return [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'production_cost' => $product->getProductionCost(),
                'quantity_storaged' => $product->getQuantityStoraged(),
            ];
        }, $products);

        return $this->json([
            'items' => $data,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalProducts
            ]
        ]);
    }

    #[Route('/search', name: 'api_products_search', methods: ['GET'])]
    public function search(Request $request, ProductsRepository $productsRepository): JsonResponse
    {
        $query = $request->query->get('q', '');
        $products = $productsRepository->createQueryBuilder('p')
            ->where('p.name LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $data = array_map(function($product) {
            return [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'production_cost' => $product->getProductionCost(),
                'quantity_storaged' => $product->getQuantityStoraged(),
            ];
        }, $products);

        return $this->json($data);
    }

    #[Route('', name: 'api_products_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $product = new Products();
        $product->setName($data['name']);
        $product->setProductionCost($data['production_cost']);
        $product->setQuantityStoraged($data['quantity_storaged']);

        $entityManager->persist($product);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'id' => $product->getId()
        ]);
    }

    #[Route('/{id}', name: 'api_products_update', methods: ['PATCH'])]
    public function update(int $id, Request $request, ProductsRepository $productsRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $product = $productsRepository->find($id);
        if (!$product) {
            return $this->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $product->setName($data['name']);
        }
        if (isset($data['production_cost'])) {
            $product->setProductionCost($data['production_cost']);
        }
        if (isset($data['quantity_storaged'])) {
            $product->setQuantityStoraged($data['quantity_storaged']);
        }

        $entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/{id}', name: 'api_products_delete', methods: ['DELETE'])]
    public function delete(int $id, ProductsRepository $productsRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $product = $productsRepository->find($id);
        if (!$product) {
            return $this->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $entityManager->remove($product);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }
} 