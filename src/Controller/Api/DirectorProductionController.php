<?php

namespace App\Controller\Api;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/director_production', name: 'api_director_production_')]
#[IsGranted('ROLE_DIRECTOR')]
class DirectorProductionController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request, ProductsRepository $productsRepository): JsonResponse
    {
        $limit = $request->query->getInt('limit', 10);
        $offset = $request->query->getInt('offset', 1);
        $sort = $request->query->get('sort', 'name');
        $order = $request->query->get('order', 'asc');
        $search = $request->query->get('search', '');

        $qb = $productsRepository->createQueryBuilder('p');
        if ($search) {
            $qb->where('p.name LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }
        $qb->orderBy('p.' . $sort, $order)
           ->setMaxResults($limit)
           ->setFirstResult($offset);
        
        $products = $qb->getQuery()->getResult();
        $totalProducts = $productsRepository->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $data = array_map(function($product) {
            return [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'production_cost' => $product->getProductionCost(),
                'quantity_storaged' => $product->getQuantityStoraged(),
            ];
        }, $products);

        return $this->json([
            'content' => $data,
            'totalElements' => $totalProducts,
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $product = new Products();
        $product->setName($data['name']);
        $product->setProductionCost($data['production_cost']);

        $entityManager->persist($product);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'id' => $product->getId()
        ]);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
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

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
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