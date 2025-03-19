<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Form\UserRolesType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/user', name: 'admin.user.')]
#[IsGranted('ROLE_ADMIN')]
class UserController extends AbstractController
{
    private const ROLES = [
        'ROLE_USER' => 'Utilisateur',
        'ROLE_EDITOR' => 'Éditeur',
        'ROLE_ADMIN' => 'Administrateur',
        'ROLE_SUPER_ADMIN' => 'Super Administrateur',
        'ROLE_ANNONCE' => 'Annonce'     // Nouveau rôle
    ];

    #[Route('/', name: 'index')]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('admin/user/index.html.twig', [
            'users' => $userRepository->findAll(),
            'available_roles' => self::ROLES
        ]);
    }

    #[Route('/{id}/edit', name: 'edit')]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserRolesType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Rôles modifiés avec succès');
            return $this->redirectToRoute('admin.user.index');
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }
}