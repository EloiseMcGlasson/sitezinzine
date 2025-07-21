<?php

namespace App\Controller\Admin;

use App\Entity\Diffusion;
use App\Form\DiffusionType;
use App\Repository\DiffusionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/admin/diffusion", name: 'admin.diffusion.')]
#[IsGranted("ROLE_ADMIN")]
#[IsGranted("ROLE_SUPER_ADMIN")]
class DiffusionController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(DiffusionRepository $diffusionRepository): Response
    {
        return $this->render('admin/diffusion/index.html.twig', [
            'diffusions' => $diffusionRepository->findLatest(10),
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $diffusion = new Diffusion();
        $form = $this->createForm(DiffusionType::class, $diffusion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($diffusion);
            $entityManager->flush();

            return $this->redirectToRoute('admin.diffusion.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/diffusion/create.html.twig', [
            'diffusion' => $diffusion,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Diffusion $diffusion): Response
    {
        return $this->render('admin/diffusion/show.html.twig', [
            'diffusion' => $diffusion,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Diffusion $diffusion, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DiffusionType::class, $diffusion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin.diffusion.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/diffusion/edit.html.twig', [
            'diffusion' => $diffusion,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Diffusion $diffusion, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$diffusion->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($diffusion);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin.diffusion.index', [], Response::HTTP_SEE_OTHER);
    }
}
