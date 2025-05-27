<?php

namespace App\Controller;

use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminViewController extends AbstractController
{
    #[Route('/admin_view_products', name: 'admin_view_products')]
    public function adminViewProducts(Request $request, ProductsRepository $productsRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $products = $productsRepository->findBy([], [], $limit, $offset);
        $totalProducts = $productsRepository->count([]);
        $totalPages = ceil($totalProducts / $limit);

        return $this->render('admin/_products.html.twig', [
            'products' => $products,
            'current_page' => $page,
            'total_pages' => $totalPages,
        ]);
    }
} 