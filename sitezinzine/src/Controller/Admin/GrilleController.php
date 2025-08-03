<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/admin/grille", name: 'admin.grille.')]
#[IsGranted("ROLE_ADMIN")]
#[IsGranted("ROLE_SUPER_ADMIN")]
class GrilleController extends AbstractController
{

    #[Route('/{startOfWeek?}', name: 'index')]
public function index(?string $startOfWeek): Response
{
    // Si une date est passée, on l'utilise, sinon on prend aujourd'hui
    $startDate = $startOfWeek
        ? \DateTime::createFromFormat('Y-m-d', $startOfWeek)
        : new \DateTime();

    // On aligne sur le mardi de la semaine
    $startOfWeek = $startDate->modify('this week')->modify('+1 day')->setTime(0, 0);

    return $this->render('admin/grille/index.html.twig', [
        'startOfWeek' => $startOfWeek,
    ]);
}

}
