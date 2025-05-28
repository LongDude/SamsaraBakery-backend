<?php

namespace App\Controller;

use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/tables/director', name: 'director_table_')]
#[IsGranted('ROLE_DIRECTOR')]
class DirectorViewController extends AbstractController
{
    #[Route('/orders', name: 'orders')]
    public function directorViewOrders(): Response
    {
        return $this->render('director/_director_orders.html.twig', []);
    }

    #[Route('/orders/js', name: 'orders_js')]
    public function directorViewOrdersJs(): Response
    {
        $response = $this->render('director/_director_orders.js.twig', []);
        $response->headers->set('Content-Type', 'application/javascript');
        return $response;
    }
}
