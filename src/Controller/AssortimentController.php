<?php

namespace App\Controller;

use App\Entity\Assortiment;
use App\Form\AssortimentForm;
use App\Repository\AssortimentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/assortiment')]
final class AssortimentController extends AbstractController
{
    #[Route(name: 'app_assortiment_index', methods: ['GET'])]
    public function index(AssortimentRepository $assortimentRepository): Response
    {
        return $this->render('assortiment/index.html.twig', [
            'assortiments' => $assortimentRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_assortiment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $assortiment = new Assortiment();
        $form = $this->createForm(AssortimentForm::class, $assortiment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($assortiment);
            $entityManager->flush();

            return $this->redirectToRoute('app_assortiment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('assortiment/new.html.twig', [
            'assortiment' => $assortiment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_assortiment_show', methods: ['GET'])]
    public function show(Assortiment $assortiment): Response
    {
        return $this->render('assortiment/show.html.twig', [
            'assortiment' => $assortiment,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_assortiment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Assortiment $assortiment, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AssortimentForm::class, $assortiment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_assortiment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('assortiment/edit.html.twig', [
            'assortiment' => $assortiment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_assortiment_delete', methods: ['POST'])]
    public function delete(Request $request, Assortiment $assortiment, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$assortiment->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($assortiment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_assortiment_index', [], Response::HTTP_SEE_OTHER);
    }
}
