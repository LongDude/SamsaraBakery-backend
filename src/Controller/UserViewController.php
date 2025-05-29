<?php

namespace App\Controller;

use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/tables/user', name: 'user_table_')]
#[IsGranted('ROLE_USER')]
class UserViewController extends AbstractController
{
    #[Route('/products', name: 'products')]
    public function userViewProducts(): Response
    {
        return $this->render('user/_user_products.html.twig', []);
    }

    #[Route('/products/js', name: 'products_js')]
    public function userViewProductsJs(): Response
    {
        $response = $this->render('user/_user_products.js.twig', []);
        $response->headers->set('Content-Type', 'application/javascript');
        return $response;
    }
}
