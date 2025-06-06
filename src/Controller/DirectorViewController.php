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

    #[Route('/affiliates_finance', name: 'affiliates_finance')]
    public function directorViewAffiliatesFinance(): Response
    {
        return $this->render('director/_director_affiliates_finance.html.twig', []);
    }

    #[Route('/affiliates_finance/js', name: 'affiliates_finance_js')]
    public function directorViewAffiliatesFinanceJs(): Response
    {
        $response = $this->render('director/_director_affiliates_finance.js.twig', []);
        $response->headers->set('Content-Type', 'application/javascript');
        return $response;
    }

    #[Route('/production_report', name: 'production_report')]
    public function directorViewProductionReport(): Response
    {
        return $this->render('director/_director_production_report.html.twig', []);
    }

    #[Route('/production_report/js', name: 'production_report_js')]
    public function directorViewProductionReportJs(): Response
    {
        $response = $this->render('director/_director_production_report.js.twig', []);
        $response->headers->set('Content-Type', 'application/javascript');
        return $response;
    }

    #[Route('/products', name: 'products')]
    public function directorViewProducts(): Response
    {
        return $this->render('director/_director_products.html.twig', []);
    }

    #[Route('/products/js', name: 'products_js')]   
    public function directorViewProductsJs(): Response
    {
        $response = $this->render('director/_director_products.js.twig', []);
        $response->headers->set('Content-Type', 'application/javascript');
        return $response;
    }
}
