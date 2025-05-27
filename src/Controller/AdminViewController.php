<?php

namespace App\Controller;

use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/tables/admin', name: 'admin_table_')]
#[IsGranted('ROLE_ADMIN')]
class AdminViewController extends AbstractController
{
    #[Route('/products', name: 'products')]
    public function adminViewProducts(): Response
    {
        return $this->render('admin/tables/_products.html.twig', []);
    }

    #[Route('/products/js', name: 'products_js')]
    public function adminViewProductsJs(): Response
    {
        $response = $this->render('admin/scripts/_products.js.twig', []);
        $response->headers->set('Content-Type', 'application/javascript');
        return $response;
    }
} 