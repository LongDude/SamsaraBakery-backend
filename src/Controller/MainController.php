<?php

namespace App\Controller;

use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(): Response
    {
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }


    #[Route('/test', name: 'app_test')]
    public function testpage(ProductsRepository $productsRepository): Response
    {
        $products = $productsRepository->findAll();
        return $this->render('main/test.html.twig', [
            'products' => $products,
        ]);
    }
}
