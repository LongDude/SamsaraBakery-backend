<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductIngredientsController extends AbstractController
{
    #[Route('/product/ingredients', name: 'app_product_ingredients')]
    public function index(): Response
    {
        return $this->render('product_ingredients/index.html.twig', [
            'controller_name' => 'ProductIngredientsController',
        ]);
    }
}
