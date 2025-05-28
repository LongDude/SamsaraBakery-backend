<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin_view_users')]
#[IsGranted('ROLE_ADMIN')]
class AdminUsersController extends AbstractController
{
    #[Route('', name: 'api_admin_users_list', methods: ['GET'])]
    public function list(Request $request, UserRepository $userRepository): JsonResponse
    {
        $limit = $request->query->getInt('limit', 10);
        $offset = $request->query->getInt('offset', 1);
        $sort = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'asc');
        $search = $request->query->get('search', '');

        $qb = $userRepository->createQueryBuilder('u');
        if ($search) {
            $qb->where('u.email LIKE :search')
               ->orWhere('u.username LIKE :search')
               ->orWhere('u.phone LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }
        $qb->orderBy('u.' . $sort, $order)
           ->setMaxResults($limit)
           ->setFirstResult($offset);
        
        $users = $qb->getQuery()->getResult();
        $totalUsers = $userRepository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $data = array_map(function($user) {
            return [
                'id' => $user->getId(),
                'email' => $user->getEmail() ?? '',
                'username' => $user->getUsername() ?? '',
                'phone' => $user->getPhone() ?? '',
                'roles' => array_filter($user->getRoles(), function($role) {
                    return $role !== 'ROLE_USER';
                }),
                'password' => '********'
            ];
        }, $users);

        return $this->json([
            'content' => $data,
            'totalElements' => $totalUsers,
        ]);
    }

    #[Route('', name: 'api_admin_users_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setEmail($data['email']);
        $user->setUsername($data['username']);
        $user->setPhone($data['phone']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        $user->setRoles($data['roles']);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'id' => $user->getId()
        ]);
    }


    #[Route('/{id}', name: 'api_admin_users_update', methods: ['PUT'])]
    public function update(int $id, Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = $userRepository->find($id);
        if (!$user) {
            return $this->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['username'])) {
            $user->setUsername($data['username']);
        }
        if (isset($data['phone'])) {
            $user->setPhone($data['phone']);
        }
        if (!empty($data['password']) &&  !preg_match('/^\*+$/', $data['password'])) {
            $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        }
        if (isset($data['roles'])) {
            $user->setRoles($data['roles']);
        }

        $entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/{id}', name: 'api_admin_users_delete', methods: ['DELETE'])]
    public function delete(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $userRepository->find($id);
        if (!$user) {
            return $this->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }
} 