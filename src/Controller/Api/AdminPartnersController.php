<?php

namespace App\Controller\Api;

use App\Entity\Partners;
use App\Repository\PartnersRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin_view_partners')]
#[IsGranted('ROLE_ADMIN')]
class AdminPartnersController extends AbstractController
{   
    #[Route('', name: 'api_admin_partners_list', methods: ['GET'])]
    public function list(Request $request, PartnersRepository $partnersRepository): JsonResponse
    {
        $limit = $request->query->getInt('limit', 10);
        $offset = $request->query->getInt('offset', 1);
        $sort = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'asc');
        $search = $request->query->get('search', '');

        $qb = $partnersRepository->createQueryBuilder('pa');
        if ($search) {
            $qb->where('pa.firmname LIKE :search')
               ->orWhere('pa.address LIKE :search')
               ->orWhere('pa.contact_number LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        $counterQb = clone $qb;
        $totalPartners = $counterQb
            ->select('COUNT(pa.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $qb->orderBy('pa.' . $sort, $order)
           ->setMaxResults($limit)
           ->setFirstResult($offset);
        $partners = $qb->getQuery()->getResult();
        
        $data = array_map(function($partners) {
            return [
                'id' => $partners->getId(),
                'firmname' => $partners->getFirmname(),
                'address' => $partners->getAddress(),
                'contact_number' => $partners->getContactNumber(),
                'representatives' => array_map(function($u) {
                    return [
                        'id' => $u->getId(),
                        'username' => $u->getUsername(),
                        'email' => $u->getEmail(),
                    ];
                }, $partners->getRepresentatives()->toArray()),
            ];
        }, $partners);

        return $this->json([
            'content' => $data,
            'totalElements' => $totalPartners,
        ]);
    }

    #[Route('', name: 'api_admin_partners_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $partner = new Partners();
        $partner->setFirmname($data['firmname']);
        $partner->setAddress($data['address']);
        $partner->setContactNumber($data['contact_number']);
        if (!empty($data['representatives']) && is_array($data['representatives'])) {
            foreach ($data['representatives'] as $userId) {
                $user = $userRepository->find($userId);
                if ($user) {
                    $partner->addRepresentative($user);
                }
            }
        }
        $entityManager->persist($partner);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'id' => $partner->getId()
        ]);
    }

    #[Route('/{id}', name: 'api_admin_partners_update', methods: ['PUT'])]
    public function update(int $id, Request $request, PartnersRepository $partnersRepository, EntityManagerInterface $entityManager, UserRepository $userRepository): JsonResponse
    {
        $partner = $partnersRepository->find($id);
        if (!$partner) {
            return $this->json(['success' => false, 'message' => 'Partner not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['firmname'])) {
            $partner->setFirmname($data['firmname']);
        }
        if (isset($data['address'])) {
            $partner->setAddress($data['address']);
        }
        if (isset($data['contact_number'])) {
            $partner->setContactNumber($data['contact_number']);
        }
        if (isset($data['representatives']) && is_array($data['representatives'])) {
            // Сбросить старых представителей
            foreach ($partner->getRepresentatives() as $oldRep) {
                $partner->removeRepresentative($oldRep);
            }
            // Добавить новых
            foreach ($data['representatives'] as $userId) {
                $user = $userRepository->find($userId);
                if ($user) {
                    $partner->addRepresentative($user);
                }
            }
        }
        $entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/{id}', name: 'api_admin_partners_delete', methods: ['DELETE'])]
    public function delete(int $id, PartnersRepository $partnersRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $partner = $partnersRepository->find($id);
        if (!$partner) {
            return $this->json(['success' => false, 'message' => 'Partner not found'], 404);
        }

        $entityManager->remove($partner);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/{id}/search_representatives', name: 'api_admin_partners_search_representatives', methods: ['GET'])]
    public function searchRepresentatives(int $id, Request $request, PartnersRepository $partnersRepository, UserRepository $userRepository): JsonResponse
    {
        $search = $request->query->get('q', '');
        $partner = $partnersRepository->find($id);
        $assigned = [];
        if ($partner) {
            $assigned = $partner->getRepresentatives()->map(fn($u) => $u->getId())->toArray();
        }
        $qb = $userRepository->createQueryBuilder('u');
        if ($assigned) {
            $qb->where($qb->expr()->notIn('u.id', ':assigned'));
            $qb->setParameter('assigned', $assigned);
        }
        if ($search) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('u.Username', ':search'),
                    $qb->expr()->like('u.email', ':search')
                )
            );
            $qb->setParameter('search', "%$search%");
        }
        $qb->orderBy('u.Username', 'asc')->setMaxResults(10);
        $users = $qb->getQuery()->getResult();
        $data = array_map(function($user) {
            return [
                'id' => $user->getId(),
                'text' => ($user->getUsername() ?? '').' ('.$user->getEmail().')',
            ];
        }, $users);
        return $this->json($data);
    }
} 