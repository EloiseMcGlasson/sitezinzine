<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\DiffusionRepository;

// src/Controller/Admin/GrilleController.php

#[Route("/admin/grille", name: 'admin.grille.')]
#[IsGranted("ROLE_ADMIN")]
#[IsGranted("ROLE_SUPER_ADMIN")]
class GrilleController extends AbstractController
{
#[Route('/{startOfWeek?}', name: 'index')]
public function index(?string $startOfWeek, DiffusionRepository $diffusionRepo): Response
{
    // 1) Date point de départ (ta logique "mardi 00:00")
    $startDate = $startOfWeek
        ? \DateTime::createFromFormat('Y-m-d', $startOfWeek)
        : new \DateTime();

    // Aligner sur mardi 00:00
    $startOfWeekDate = (clone $startDate)->modify('this week')->modify('+1 day')->setTime(0, 0, 0);
    $endOfWeekDate   = (clone $startOfWeekDate)->modify('+7 days');

    // 2) Génère les 7 jours (mardi → lundi)
    $jours = [];
    for ($i = 0; $i < 7; $i++) {
        $jours[] = (clone $startOfWeekDate)->modify("+$i days");
    }

    // 3) Récupère les diffusions de la semaine
    $diffusions = $diffusionRepo->findByWeek($startOfWeekDate, $endOfWeekDate);

    // 4) Construit la structure attendue par le Twig "post-it"
    // daySegments[dayIndex][] = [ title, duration (min), startIndex (0..95) ]
    $daySegments = array_fill(0, 7, []);

    foreach ($diffusions as $diffusion) {
        $start = (clone $diffusion->getHoraireDiffusion())->setTime(
            (int) $diffusion->getHoraireDiffusion()->format('H'),
            (int) $diffusion->getHoraireDiffusion()->format('i'),
            0
        );

        // Ignore hors semaine (sécurité si la requête en renvoie)
        if ($start < $startOfWeekDate || $start >= $endOfWeekDate) {
            continue;
        }

        // Calcule l'index du jour (0..6) par rapport au mardi 00:00
        $interval = $startOfWeekDate->diff($start);
        $dayIndex = (int) $interval->days;
        if ($dayIndex < 0 || $dayIndex > 6) {
            continue;
        }

        // Index de quart d'heure de début (0..95)
        $hour    = (int) $start->format('H');
        $minute  = (int) $start->format('i');
        $startIndex = $hour * 4 + intdiv($minute, 15);
        if ($startIndex < 0)   { $startIndex = 0; }
        if ($startIndex > 95)  { $startIndex = 95; }

        $emission = $diffusion->getEmission();
        // Durée en minutes (min 1 min => on plafonne visuel à au moins 1 slot)
        $duration = max(1, (int) $emission->getDuree());

        $daySegments[$dayIndex][] = [
            'title'      => $emission->getTitre(),
            'duration'   => $duration,      // p.ex. 72 → height = ceil(72/15)*8px en CSS/JS
            'startIndex' => $startIndex,    // 0..95
        ];
    }

    return $this->render('admin/grille/index.html.twig', [
        'startOfWeek' => $startOfWeekDate,
        'jours'       => $jours,
        'daySegments' => $daySegments,
    ]);
}

}

