<?php

namespace App\Controller;

use App\Entity\ProductsMovement;
use App\Form\ProductsMovementForm;
use App\Repository\ProductsMovementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/products/movement')]
final class ProductsMovementController extends AbstractController
{
    #[Route(name: 'app_products_movement_index', methods: ['GET'])]
    public function index(ProductsMovementRepository $productsMovementRepository): Response
    {
        return $this->render('products_movement/index.html.twig', [
            'products_movements' => $productsMovementRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_products_movement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $productsMovement = new ProductsMovement();
        $form = $this->createForm(ProductsMovementForm::class, $productsMovement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($productsMovement);
            $entityManager->flush();

            return $this->redirectToRoute('app_products_movement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('products_movement/new.html.twig', [
            'products_movement' => $productsMovement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_products_movement_show', methods: ['GET'])]
    public function show(ProductsMovement $productsMovement): Response
    {
        return $this->render('products_movement/show.html.twig', [
            'products_movement' => $productsMovement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_products_movement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ProductsMovement $productsMovement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductsMovementForm::class, $productsMovement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_products_movement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('products_movement/edit.html.twig', [
            'products_movement' => $productsMovement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_products_movement_delete', methods: ['POST'])]
    public function delete(Request $request, ProductsMovement $productsMovement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$productsMovement->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($productsMovement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_products_movement_index', [], Response::HTTP_SEE_OTHER);
    }
}
