<?php

namespace App\Controller;

use App\Entity\Partners;
use App\Form\PartnersForm;
use App\Repository\PartnersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/partners')]
final class PartnersController extends AbstractController
{
    #[Route(name: 'app_partners_index', methods: ['GET'])]
    public function index(PartnersRepository $partnersRepository): Response
    {
        return $this->render('partners/index.html.twig', [
            'partners' => $partnersRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_partners_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $partner = new Partners();
        $form = $this->createForm(PartnersForm::class, $partner);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($partner);
            $entityManager->flush();

            return $this->redirectToRoute('app_partners_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('partners/new.html.twig', [
            'partner' => $partner,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_partners_show', methods: ['GET'])]
    public function show(Partners $partner): Response
    {
        return $this->render('partners/show.html.twig', [
            'partner' => $partner,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_partners_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Partners $partner, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PartnersForm::class, $partner);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_partners_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('partners/edit.html.twig', [
            'partner' => $partner,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_partners_delete', methods: ['POST'])]
    public function delete(Request $request, Partners $partner, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$partner->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($partner);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_partners_index', [], Response::HTTP_SEE_OTHER);
    }
}
