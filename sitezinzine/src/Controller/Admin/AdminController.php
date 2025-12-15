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
    $users = $userRepository->findAll();

    // Tri alphabétique par username (insensible à la casse)
    usort($users, static function ($a, $b) {
        return strcmp(
            mb_strtolower($a->getUsername() ?? ''),
            mb_strtolower($b->getUsername() ?? '')
        );
    });

    return $this->render('admin/index.html.twig', [
        'user'  => $user,
        'users' => $users,
    ]);
}




}