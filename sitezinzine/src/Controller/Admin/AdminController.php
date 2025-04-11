<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\UserRepository;


#[Route("/admin", name: 'admin.')]
#[IsGranted('ROLE_USER')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(UserRepository $userRepository): Response
    {
        $user = $this->getUser();
    return $this->render('admin/index.html.twig', [
        'user' => $user,
        'users' => $userRepository->findAll() // Ajout de tous les utilisateurs
        ]);
    }



}