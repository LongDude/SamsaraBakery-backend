<?php

namespace App\Controller;

use App\Entity\Affiliates;
use App\Form\AffiliatesForm;
use App\Repository\AffiliatesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/affiliates')]
final class AffiliatesController extends AbstractController
{
    #[Route(name: 'app_affiliates_index', methods: ['GET'])]
    public function index(AffiliatesRepository $affiliatesRepository): Response
    {
        return $this->render('affiliates/index.html.twig', [
            'affiliates' => $affiliatesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_affiliates_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $affiliate = new Affiliates();
        $form = $this->createForm(AffiliatesForm::class, $affiliate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($affiliate);
            $entityManager->flush();

            return $this->redirectToRoute('app_affiliates_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('affiliates/new.html.twig', [
            'affiliate' => $affiliate,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_affiliates_show', methods: ['GET'])]
    public function show(Affiliates $affiliate): Response
    {
        return $this->render('affiliates/show.html.twig', [
            'affiliate' => $affiliate,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_affiliates_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Affiliates $affiliate, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AffiliatesForm::class, $affiliate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_affiliates_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('affiliates/edit.html.twig', [
            'affiliate' => $affiliate,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_affiliates_delete', methods: ['POST'])]
    public function delete(Request $request, Affiliates $affiliate, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$affiliate->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($affiliate);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_affiliates_index', [], Response::HTTP_SEE_OTHER);
    }
}
