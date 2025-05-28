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

    #[Route('/ingredients', name: 'ingredients')]
    public function adminViewIngredients(): Response
    {
        return $this->render('admin/tables/_ingredients.html.twig', []);
    }

    #[Route('/ingredients/js', name: 'ingredients_js')]
    public function adminViewIngredientsJs(): Response
    {
        $response = $this->render('admin/scripts/_ingredients.js.twig', []);
        $response->headers->set('Content-Type', 'application/javascript');
        return $response;
    }

    #[Route('/suppliers', name: 'suppliers')]
    public function adminViewSuppliers(): Response
    {
        return $this->render('admin/tables/_suppliers.html.twig', []);
    }

    #[Route('/suppliers/js', name: 'suppliers_js')]
    public function adminViewSuppliersJs(): Response
    {
        $response = $this->render('admin/scripts/_suppliers.js.twig', []);
        $response->headers->set('Content-Type', 'application/javascript');
        return $response;
    }

    #[Route('/partners', name: 'partners')]
    public function adminViewPartners(): Response
    {
        return $this->render('admin/tables/_partners.html.twig', []);
    }

    #[Route('/partners/js', name: 'partners_js')]
    public function adminViewPartnersJs(): Response
    {
        $response = $this->render('admin/scripts/_partners.js.twig', []);
        $response->headers->set('Content-Type', 'application/javascript');
        return $response;
    }

    #[Route('/users', name: 'users')]
    public function adminViewUsers(): Response
    {
        return $this->render('admin/tables/_users.html.twig', []);
    }

    #[Route('/users/js', name: 'users_js')]
    public function adminViewUsersJs(): Response
    {
        $response = $this->render('admin/scripts/_users.js.twig', []);
        $response->headers->set('Content-Type', 'application/javascript');
        return $response;
    }

    #[Route('/affiliates', name: 'affiliates')]
    public function adminViewAffiliates(): Response
    {
        return $this->render('admin/tables/_affiliates.html.twig', []);
    }

    #[Route('/affiliates/js', name: 'affiliates_js')]
    public function adminViewAffiliatesJs(): Response
    {
        $response = $this->render('admin/scripts/_affiliates.js.twig', []);
        $response->headers->set('Content-Type', 'application/javascript');
        return $response;
    }
} 