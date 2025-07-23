<?php

namespace App\Controller\Admin;

use App\Entity\InviteOldAnimateur;
use App\Repository\InviteOldAnimateurRepository;
use App\Form\InviteOldAnimateurType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Requirement\Requirement;


#[Route("/admin/InviteOldAnimateur", name: 'admin.InviteOldAnimateur.')]
#[IsGranted("ROLE_USER")]
class InviteOldAnimateurController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(Request $request, InviteOldAnimateurRepository $InviteOldAnimateurRepository): Response
    {
        $InviteOldAnimateur = $InviteOldAnimateurRepository->findAll();
        return $this->render('admin/InviteOldAnimateur/index.html.twig', [
            'InviteOldAnimateurs' => $InviteOldAnimateur
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function show(InviteOldAnimateur $InviteOldAnimateur, int $id, InviteOldAnimateurRepository $InviteOldAnimateurRepository)
    {
        $InviteOldAnimateur = $InviteOldAnimateurRepository->find($id);
        return $this->render('admin/InviteOldAnimateur/show.html.twig', [
            'InviteOldAnimateur' => $InviteOldAnimateur,
            
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => Requirement::DIGITS])]
    public function edit(InviteOldAnimateur $InviteOldAnimateur, Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(InviteOldAnimateurType::class, $InviteOldAnimateur);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
           
            $em->flush();
            $this->addFlash('success', 'L\'InviteOldAnimateur a bien été modifié');
            return $this->redirectToRoute('admin.InviteOldAnimateur.index');
        }
        return $this->render('admin/InviteOldAnimateur/edit.html.twig', [
            'InviteOldAnimateur' => $InviteOldAnimateur,
            'form' => $form
        ]);
    }

    #[Route('/create', name: 'create')]
    public function create(Request $request, EntityManagerInterface $em)
    {
        $InviteOldAnimateur = new InviteOldAnimateur();
        $form = $this->createForm(InviteOldAnimateurType::class, $InviteOldAnimateur);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $em->persist($InviteOldAnimateur);
            $em->flush();
            $this->addFlash('success', 'L\'invité a été crée !');
            return $this->redirectToRoute('admin.InviteOldAnimateur.index');
        }
        return $this->render('admin/InviteOldAnimateur/create.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => Requirement::DIGITS])]
    public function remove(InviteOldAnimateur $InviteOldAnimateur, EntityManagerInterface $em)
    {
        $em->remove($InviteOldAnimateur);
        $em->flush();
        $this->addFlash('success', 'L\'invité a bien été supprimé');
        return $this->redirectToRoute('admin.InviteOldAnimateur.index');
    }
}
