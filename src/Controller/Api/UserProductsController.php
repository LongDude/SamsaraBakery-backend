<?php

namespace App\Controller\Api;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use App\Repository\Views\UserProductsViewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/user_products', name: 'api_user_products_')]
#[IsGranted('ROLE_USER')]
class UserProductsController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request, UserProductsViewRepository $userProductsViewRepository): JsonResponse
    {
        $limit = $request->query->getInt('limit', 10);
        $offset = $request->query->getInt('offset', 1);
        $sort = $request->query->get('sort', 'product');
        $order = $request->query->get('order', 'asc');
        $search = $request->query->get('search', '');

        $qb = $userProductsViewRepository->createQueryBuilder('u');
        if ($search) {
            $qb->where('u.product LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }
        $qb->orderBy('u.' . $sort, $order)
           ->setMaxResults($limit)
           ->setFirstResult($offset);
        
        $products = $qb->getQuery()->getResult();
        $totalProducts = $userProductsViewRepository->createQueryBuilder('u')
            ->select('COUNT(u.product)')
            ->getQuery()
            ->getSingleScalarResult();

        $data = array_map(function($user) {
            return [
                'product' => $user->getProduct(),
                'price' => $user->getPrice(),
                'quantity' => $user->getQuantity(),
            ];
        }, $products);

        return $this->json([
            'content' => $data,
            'totalElements' => $totalProducts,
        ]);
    }
} 