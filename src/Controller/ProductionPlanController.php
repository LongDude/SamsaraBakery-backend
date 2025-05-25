<?php

namespace App\Controller;

use App\Entity\ProductionPlan;
use App\Form\ProductionPlanForm;
use App\Repository\ProductionPlanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/production/plan')]
final class ProductionPlanController extends AbstractController
{
    #[Route(name: 'app_production_plan_index', methods: ['GET'])]
    public function index(ProductionPlanRepository $productionPlanRepository): Response
    {
        return $this->render('production_plan/index.html.twig', [
            'production_plans' => $productionPlanRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_production_plan_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $productionPlan = new ProductionPlan();
        $form = $this->createForm(ProductionPlanForm::class, $productionPlan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($productionPlan);
            $entityManager->flush();

            return $this->redirectToRoute('app_production_plan_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('production_plan/new.html.twig', [
            'production_plan' => $productionPlan,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_production_plan_show', methods: ['GET'])]
    public function show(ProductionPlan $productionPlan): Response
    {
        return $this->render('production_plan/show.html.twig', [
            'production_plan' => $productionPlan,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_production_plan_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ProductionPlan $productionPlan, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductionPlanForm::class, $productionPlan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_production_plan_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('production_plan/edit.html.twig', [
            'production_plan' => $productionPlan,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_production_plan_delete', methods: ['POST'])]
    public function delete(Request $request, ProductionPlan $productionPlan, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$productionPlan->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($productionPlan);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_production_plan_index', [], Response::HTTP_SEE_OTHER);
    }
}
