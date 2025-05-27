<?php

namespace App\Controller\Api;

use App\Entity\Ingredients;
use App\Repository\IngredientsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin_view_ingredients')]
#[IsGranted('ROLE_ADMIN')]
class AdminIngredientsController extends AbstractController
{
    #[Route('', name: 'api_admin_ingredients_list', methods: ['GET'])]
    public function list(Request $request, IngredientsRepository $ingredientsRepository): JsonResponse
    {
        $limit = $request->query->getInt('limit', 10);
        $offset = $request->query->getInt('offset', 1);
        $sort = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'asc');
        $search = $request->query->get('search', '');

        $qb = $ingredientsRepository->createQueryBuilder('i');
        if ($search) {
            $qb->where('i.name LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }
        $qb->orderBy('i.' . $sort, $order)
           ->setMaxResults($limit)
           ->setFirstResult($offset);
        
        $ingredients = $qb->getQuery()->getResult();
        $totalIngredients = $ingredientsRepository->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $data = array_map(function($ingredients) {
            return [
                'id' => $ingredients->getId(),
                'name' => $ingredients->getName(),
                'quantity' => $ingredients->getQuantity(),
            ];
        }, $ingredients);

        return $this->json([
            'content' => $data,
            'totalElements' => $totalIngredients,
        ]);
    }

    #[Route('', name: 'api_admin_ingredients_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $ingredient = new Ingredients();
        $ingredient->setName($data['name']);
        $ingredient->setQuantity($data['quantity']);

        $entityManager->persist($ingredient);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'id' => $ingredient->getId()
        ]);
    }

    #[Route('/{id}', name: 'api_admin_ingredients_update', methods: ['PUT'])]
    public function update(int $id, Request $request, IngredientsRepository $ingredientsRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $ingredient = $ingredientsRepository->find($id);
        if (!$ingredient) {
            return $this->json(['success' => false, 'message' => 'Ingredient not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $ingredient->setName($data['name']);
        }
        if (isset($data['quantity'])) {
            $ingredient->setQuantity($data['quantity']);
        }

        $entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/{id}', name: 'api_admin_ingredients_delete', methods: ['DELETE'])]
    public function delete(int $id, IngredientsRepository $ingredientsRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $ingredient = $ingredientsRepository->find($id);
        if (!$ingredient) {
            return $this->json(['success' => false, 'message' => 'Ingredient not found'], 404);
        }

        $entityManager->remove($ingredient);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }
} 