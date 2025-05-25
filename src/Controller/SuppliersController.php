<?php

namespace App\Controller;

use App\Entity\Suppliers;
use App\Form\SuppliersForm;
use App\Repository\SuppliersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/suppliers')]
final class SuppliersController extends AbstractController
{
    #[Route(name: 'app_suppliers_index', methods: ['GET'])]
    public function index(SuppliersRepository $suppliersRepository): Response
    {
        return $this->render('suppliers/index.html.twig', [
            'suppliers' => $suppliersRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_suppliers_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $supplier = new Suppliers();
        $form = $this->createForm(SuppliersForm::class, $supplier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($supplier);
            $entityManager->flush();

            return $this->redirectToRoute('app_suppliers_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('suppliers/new.html.twig', [
            'supplier' => $supplier,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_suppliers_show', methods: ['GET'])]
    public function show(Suppliers $supplier): Response
    {
        return $this->render('suppliers/show.html.twig', [
            'supplier' => $supplier,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_suppliers_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Suppliers $supplier, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SuppliersForm::class, $supplier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_suppliers_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('suppliers/edit.html.twig', [
            'supplier' => $supplier,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_suppliers_delete', methods: ['POST'])]
    public function delete(Request $request, Suppliers $supplier, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$supplier->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($supplier);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_suppliers_index', [], Response::HTTP_SEE_OTHER);
    }
}
