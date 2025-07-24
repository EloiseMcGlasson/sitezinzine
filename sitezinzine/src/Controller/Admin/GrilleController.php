<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;

#[Route("/admin/grille", name: 'admin.grille.')]
#[IsGranted("ROLE_ADMIN")]
#[IsGranted("ROLE_SUPER_ADMIN")]
class GrilleController extends AbstractController
{

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        // On prend aujourd'hui
        $now = new \DateTimeImmutable();

        // On calcule le lundi de la semaine en cours
        $startOfWeek = $now->modify(('Monday' === $now->format('l')) ? 'this monday' : 'last monday');
        $startOfWeek = $startOfWeek->setTime(0, 0); // On met l'heure à 00:00

        return $this->render('admin/grille/index.html.twig', [
            'startOfWeek' => $startOfWeek,
        ]);
    }
}
