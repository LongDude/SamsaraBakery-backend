<?php

namespace App\Controller\Api;

use App\Entity\Affiliates;
use App\Entity\Products;
use App\Repository\AffiliatesRepository;
use App\Repository\ProductsRepository;
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
        $qb->orderBy('a.' . $sort, $order)
           ->setMaxResults($limit)
           ->setFirstResult($offset);
        
        $affiliates = $qb->getQuery()->getResult();
        $totalAffiliates = $affiliatesRepository->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $data = array_map(function($affiliate) {
            return [
                'id' => $affiliate->getId(),
                'address' => $affiliate->getAddress(),
                'contact_number' => $affiliate->getContactNumber(),
                'manager' => $affiliate->getManager()->getUsername(),
            ];
        }, $affiliates);

        return $this->json([
            'content' => $data,
            'totalElements' => $totalAffiliates,
        ]);
    }

    #[Route('', name: 'api_admin_affiliates_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $affiliate = new Affiliates();
        $affiliate->setAddress($data['address']);
        $affiliate->setContactNumber($data['contact_number']);
        $affiliate->setManager($data['manager']);

        $entityManager->persist($affiliate);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'id' => $affiliate->getId()
        ]);
    }

    #[Route('/{id}', name: 'api_admin_affiliates_update', methods: ['PUT'])]
    public function update(int $id, Request $request, AffiliatesRepository $affiliatesRepository, EntityManagerInterface $entityManager): JsonResponse
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
        if (isset($data['manager'])) {
            $affiliate->setManager($data['manager']);
        }

        $entityManager->flush();

        return $this->json(['success' => true]);
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