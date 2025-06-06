<?php

namespace App\Controller\Api;

use App\Entity\Affiliates;
use App\Entity\Products;
use App\Repository\AffiliatesRepository;
use App\Repository\ProductsRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin_view_affiliates')]
#[IsGranted('ROLE_ADMIN')]
class AdminAffiliatesController extends AbstractController
{
    #[Route('', name: 'api_admin_affiliates_list', methods: ['GET'])]
    public function list(Request $request, AffiliatesRepository $affiliatesRepository): JsonResponse
    {
        $limit = $request->query->getInt('limit', 10);
        $offset = $request->query->getInt('offset', 1);
        $sort = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'asc');
        $search = $request->query->get('search', '');

        $qb = $affiliatesRepository->createQueryBuilder('a');
        if ($search) {
            $qb->where('a.address LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        $countQb = clone $qb;

        $totalAffiliates = $countQb
        ->select('COUNT(a.id)')
        ->getQuery()
        ->getSingleScalarResult();
        
        $qb->orderBy('a.' . $sort, $order)
           ->setMaxResults($limit)
           ->setFirstResult($offset);
        
        $affiliates = $qb->getQuery()->getResult();
        
        $data = array_map(function($affiliate) {
            return [
                'id' => $affiliate->getId(),
                'address' => $affiliate->getAddress(),
                'contact_number' => $affiliate->getContactNumber(),
                'manager' => $affiliate->getManager() ? $affiliate->getManager()->getUsername() : '',
            ];
        }, $affiliates);

        return $this->json([
            'content' => $data,
            'totalElements' => $totalAffiliates,
        ]);
    }

    #[Route('', name: 'api_admin_affiliates_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $affiliate = new Affiliates();
        $affiliate->setAddress($data['address']);
        $affiliate->setContactNumber($data['contact_number']);
        if (isset($data['manager_id'])) {
            $user = $userRepository->find($data['manager_id']);
            if (!$user) {
                return $this->json(['success' => false, 'message' => 'User not found'], 404);
            }
            $affiliate->setManager($user);
        }

        $entityManager->persist($affiliate);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'id' => $affiliate->getId()
        ]);
    }

    #[Route('/{id}', name: 'api_admin_affiliates_update', methods: ['PUT'])]
    public function update(int $id, Request $request, UserRepository $userRepository, AffiliatesRepository $affiliatesRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $affiliate = $affiliatesRepository->find($id);
        if (!$affiliate) {
            return $this->json(['success' => false, 'message' => 'Affiliate not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['address'])) {
            $affiliate->setAddress($data['address']);
        }
        if (isset($data['contact_number'])) {
            $affiliate->setContactNumber($data['contact_number']);
        }
        if (isset($data['manager_id'])) {
            if ($data['manager_id'] == -1) {
                $affiliate->setManager(null);
            } else {
                $user = $userRepository->find($data['manager_id']);
                if (!$user) {
                    return $this->json(['success' => false, 'message' => 'User not found'], 404);
                }
                $affiliate->setManager($user);
            }
        }

        $entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/{id}/search_manager', name: 'api_admin_affiliates_search_manager', methods: ['GET'])]
    public function search(int $id, Request $request, AffiliatesRepository $affiliatesRepository, UserRepository $userRepository): JsonResponse
    {
        $search = $request->query->get('q', '');
        $qb = $userRepository->createQueryBuilder('u');

        // Подзапрос для занятых менеджеров
        $subQ = $affiliatesRepository->createQueryBuilder('a')
            ->select('IDENTITY(a.manager)')
            ->where('a.id != :affiliate_id')
            ->andWhere('a.manager IS NOT NULL')
            ->setParameter('affiliate_id', $id);

        $expr = $qb->expr();
        $searchCond = $expr->orX(
            $expr->like('u.Username', ':search'),
            $expr->like('u.email', ':search')
        );

        $qb->where($expr->notIn('u.id', $subQ->getDQL()))
           ->andWhere($searchCond)
           ->setParameter('search', '%' . $search . '%')
           ->setParameter('affiliate_id', $id);

        $qb->orderBy('u.Username', 'asc');
        $qb->setMaxResults(10);
        $users = $qb->getQuery()->getResult();

        $data = array_map(function($user) {
            return [
                'label' => ($user->getUsername() ?? '')."(".$user->getEmail().")",
                'value' => $user->getUsername(),
                'id' => $user->getId(),
            ];
        }, $users);
        return $this->json($data);
    }

    #[Route('/{id}', name: 'api_admin_affiliates_delete', methods: ['DELETE'])]
    public function delete(int $id, AffiliatesRepository $affiliatesRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $affiliate = $affiliatesRepository->find($id);
        if (!$affiliate) {
            return $this->json(['success' => false, 'message' => 'Affiliate not found'], 404);
        }

        $entityManager->remove($affiliate);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }
} 