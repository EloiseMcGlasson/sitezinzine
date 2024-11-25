<?php

namespace App\Controller\Admin;

use App\Entity\Invite;
use App\Repository\InviteRepository;
use App\Form\InviteType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Requirement\Requirement;


#[Route("/admin/invite", name: 'admin.invite.')]
#[IsGranted('ROLE_USER')]
class InviteController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(Request $request, InviteRepository $inviteRepository): Response
    {
        $invite = $inviteRepository->findAll();
        return $this->render('admin/invite/index.html.twig', [
            'invites' => $invite
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function show(Invite $invite, int $id, InviteRepository $inviteRepository)
    {
        $invite = $inviteRepository->find($id);
        return $this->render('admin/invite/show.html.twig', [
            'invite' => $invite,
            
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => Requirement::DIGITS])]
    public function edit(Invite $invite, Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(InviteType::class, $invite);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
           
            $em->flush();
            $this->addFlash('success', 'L\'invite a bien été modifié');
            return $this->redirectToRoute('admin.invite.index');
        }
        return $this->render('admin/invite/edit.html.twig', [
            'invite' => $invite,
            'form' => $form
        ]);
    }

    #[Route('/create', name: 'create')]
    public function create(Request $request, EntityManagerInterface $em)
    {
        $invite = new Invite();
        $form = $this->createForm(InviteType::class, $invite);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $em->persist($invite);
            $em->flush();
            $this->addFlash('success', 'L\'invité a été crée !');
            return $this->redirectToRoute('admin.invite.index');
        }
        return $this->render('admin/invite/create.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => Requirement::DIGITS])]
    public function remove(Invite $invite, EntityManagerInterface $em)
    {
        $em->remove($invite);
        $em->flush();
        $this->addFlash('success', 'L\'invité a bien été supprimé');
        return $this->redirectToRoute('admin.invite.index');
    }
}
