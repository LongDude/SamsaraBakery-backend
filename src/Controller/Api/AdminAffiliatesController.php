<?php

namespace App\Controller\Api;

use App\Entity\Affiliates;
use App\Entity\User;
use App\Repository\AffiliatesRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
#[IsGranted('ROLE_ADMIN')]
class AdminAffiliatesController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('/admin_view_affiliates', name: 'api_admin_affiliates_index', methods: ['GET'])]
    public function index(
        Request $request,
        AffiliatesRepository $affiliatesRepository
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
        $countQueryBuilder = $affiliatesRepository->createQueryBuilder('a')
            ->select('COUNT(a.id)');

        // Создаем запрос для получения данных
        $queryBuilder = $affiliatesRepository->createQueryBuilder('a')
            ->leftJoin('a.manager', 'm')
            ->addSelect('m');

        if ($search) {
            $countQueryBuilder->andWhere('a.address LIKE :search OR a.contact_number LIKE :search')
                ->setParameter('search', '%' . $search . '%');
            $queryBuilder->andWhere('a.address LIKE :search OR a.contact_number LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Получаем общее количество записей
        $totalItems = $countQueryBuilder->getQuery()->getSingleScalarResult();
        $totalPages = ceil($totalItems / $itemsPerPage);

        // Добавляем сортировку только к основному запросу
        if ($sortField) {
            if ($sortField === 'manager') {
                $queryBuilder->orderBy('m.username', $sortOrder);
            } else {
                $queryBuilder->orderBy('a.' . $sortField, $sortOrder);
            }
        }

        // Устанавливаем ограничения для текущей страницы
        $queryBuilder->setFirstResult(($page - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage);

        $affiliates = $queryBuilder->getQuery()->getResult();

        return $this->render('affiliates/_admin_table.html.twig', [
            'affiliates' => $affiliates,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_items' => $totalItems,
            'items_per_page' => $itemsPerPage
        ]);
    }

    #[Route('/admin_view_affiliates/search', name: 'api_admin_affiliates_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');
        $field = $request->query->get('field', 'address');

        if (empty($query)) {
            return $this->json(['results' => []]);
        }

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('a')
           ->from(Affiliates::class, 'a')
           ->where("a.{$field} LIKE :query")
           ->setParameter('query', "%{$query}%")
           ->setMaxResults(10);

        $results = $qb->getQuery()->getResult();

        return $this->json([
            'results' => array_map(function($affiliate) use ($field) {
                return [
                    'id' => $affiliate->getId(),
                    $field => $affiliate->{'get' . ucfirst($field)}()
                ];
            }, $results)
        ]);
    }

    #[Route('/admin_view_affiliates/search_manager', name: 'api_admin_affiliates_search_manager', methods: ['GET'])]
    public function searchManager(Request $request, UserRepository $userRepository): JsonResponse
    {
        $query = $request->query->get('q', '');

        if (empty($query)) {
            return $this->json(['results' => []]);
        }

        $users = $userRepository->createQueryBuilder('u')
            ->where('u.username LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        return $this->json([
            'results' => array_map(function(User $user) {
                return [
                    'id' => $user->getId(),
                    'username' => $user->getUsername()
                ];
            }, $users)
        ]);
    }

    #[Route('/admin_view_affiliates/{id}/edit', name: 'api_admin_affiliates_edit', methods: ['POST'])]
    public function edit(Request $request, int $id): JsonResponse
    {
        $affiliate = $this->entityManager->getRepository(Affiliates::class)->find($id);
        
        if (!$affiliate) {
            return $this->json(['success' => false, 'message' => 'Affiliate not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $field = $data['field'] ?? '';
        $value = $data['value'] ?? '';

        if (empty($field)) {
            return $this->json(['success' => false, 'message' => 'Field is required'], 400);
        }

        try {
            if ($field === 'contactNumber') {
                $affiliate->setContactNumber($value);
            } elseif ($field === 'address') {
                $affiliate->setAddress($value);
            } elseif ($field === 'manager') {
                $manager = $this->entityManager->getRepository(User::class)->find($value);
                if (!$manager) {
                    return $this->json(['success' => false, 'message' => 'Manager not found'], 404);
                }
                $affiliate->setManager($manager);
            } else {
                return $this->json(['success' => false, 'message' => 'Invalid field'], 400);
            }

            $this->entityManager->flush();
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Database error'], 500);
        }
    }

    #[Route('/admin_view_affiliates', name: 'api_admin_affiliates_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['address']) || !isset($data['contactNumber']) || !isset($data['manager'])) {
            return $this->json(['success' => false, 'message' => 'Missing required fields'], 400);
        }

        $manager = $this->entityManager->getRepository(User::class)->find($data['manager']);
        if (!$manager) {
            return $this->json(['success' => false, 'message' => 'Manager not found'], 404);
        }

        $affiliate = new Affiliates();
        $affiliate->setAddress($data['address']);
        $affiliate->setContactNumber($data['contactNumber']);
        $affiliate->setManager($manager);

        try {
            $this->entityManager->persist($affiliate);
            $this->entityManager->flush();
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Database error'], 500);
        }
    }

    #[Route('/admin_view_affiliates/{id}', name: 'api_admin_affiliates_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $affiliate = $this->entityManager->getRepository(Affiliates::class)->find($id);
        
        if (!$affiliate) {
            return $this->json(['success' => false, 'message' => 'Affiliate not found'], 404);
        }

        $this->entityManager->remove($affiliate);
        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }
} 