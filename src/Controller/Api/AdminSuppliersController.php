<?php

namespace App\Controller\Api;

use App\Entity\Suppliers;
use App\Repository\SuppliersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin_view_suppliers')]
#[IsGranted('ROLE_ADMIN')]
class AdminSuppliersController extends AbstractController
{   
    #[Route('', name: 'api_admin_suppliers_list', methods: ['GET'])]
    public function list(Request $request, SuppliersRepository $suppliersRepository): JsonResponse
    {
        $limit = $request->query->getInt('limit', 10);
        $offset = $request->query->getInt('offset', 1);
        $sort = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'asc');
        $search = $request->query->get('search', '');

        $qb = $suppliersRepository->createQueryBuilder('su');
        if ($search) {
            $qb->where('su.firmname LIKE :search')
               ->orWhere('su.address LIKE :search')
               ->orWhere('su.contact_number LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }
        $qb->orderBy('su.' . $sort, $order)
           ->setMaxResults($limit)
           ->setFirstResult($offset);
        
        $suppliers = $qb->getQuery()->getResult();
        $totalSuppliers = $suppliersRepository->createQueryBuilder('su')
            ->select('COUNT(su.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $data = array_map(function($suppliers) {
            return [
                'id' => $suppliers->getId(),
                'firmname' => $suppliers->getFirmname(),
                'address' => $suppliers->getAddress(),
                'contact_number' => $suppliers->getContactNumber(),
            ];
        }, $suppliers);

        return $this->json([
            'content' => $data,
            'totalElements' => $totalSuppliers,
        ]);
    }

    #[Route('', name: 'api_admin_suppliers_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $supplier = new Suppliers();
        $supplier->setFirmname($data['firmname']);
        $supplier->setAddress($data['address']);
        $supplier->setContactNumber($data['contact_number']);

        $entityManager->persist($supplier);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'id' => $supplier->getId()
        ]);
    }

    #[Route('/{id}', name: 'api_admin_suppliers_update', methods: ['PUT'])]
    public function update(int $id, Request $request, SuppliersRepository $suppliersRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $supplier = $suppliersRepository->find($id);
        if (!$supplier) {
            return $this->json(['success' => false, 'message' => 'Supplier not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['firmname'])) {
            $supplier->setFirmname($data['firmname']);
        }
        if (isset($data['address'])) {
            $supplier->setAddress($data['address']);
        }
        if (isset($data['contact_number'])) {
            $supplier->setContactNumber($data['contact_number']);
        }

        $entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/{id}', name: 'api_admin_suppliers_delete', methods: ['DELETE'])]
    public function delete(int $id, SuppliersRepository $suppliersRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $supplier = $suppliersRepository->find($id);
        if (!$supplier) {
            return $this->json(['success' => false, 'message' => 'Supplier not found'], 404);
        }

        $entityManager->remove($supplier);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }
} 