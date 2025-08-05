<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\DiffusionRepository;

#[Route("/admin/grille", name: 'admin.grille.')]
#[IsGranted("ROLE_ADMIN")]
#[IsGranted("ROLE_SUPER_ADMIN")]
class GrilleController extends AbstractController
{

   

#[Route('/{startOfWeek?}', name: 'index')]
public function index(?string $startOfWeek, DiffusionRepository $diffusionRepo): Response
{
    $startDate = $startOfWeek
        ? \DateTime::createFromFormat('Y-m-d', $startOfWeek)
        : new \DateTime();

    // Aligner sur mardi 00:00
    $startOfWeekDate = (clone $startDate)->modify('this week')->modify('+1 day')->setTime(0, 0);
    $endOfWeekDate = (clone $startOfWeekDate)->modify('+7 days');

    $diffusions = $diffusionRepo->findByWeek($startOfWeekDate, $endOfWeekDate);

    // Création des 7 jours (mutables)
    $jours = [];
    for ($i = 0; $i < 7; $i++) {
        $jours[] = (clone $startOfWeekDate)->modify("+$i days");
    }

    // Init slots (7 jours × 96 quarts d'heure)
    $slots = [];
    foreach ($jours as $dayIndex => $jour) {
        $slots[$dayIndex] = array_fill(0, 96, null);
    }

    // Placement des diffusions
    foreach ($diffusions as $diffusion) {
        $start = $diffusion->getHoraireDiffusion(); // DateTime
        $interval = $startOfWeekDate->diff($start);
        $dayIndex = (int) $interval->days;

        if ($dayIndex < 0 || $dayIndex > 6) continue;

        $hour = (int) $start->format('H');
        $minute = (int) $start->format('i');
        $quarterIndex = $hour * 4 + intdiv($minute, 15);
        $duration = $diffusion->getEmission()->getDuree(); // en minutes
        $length = (int) ceil($duration / 15);

        $slots[$dayIndex][$quarterIndex] = $diffusion;

        // Marquer les quarts suivants comme "covered"
        for ($i = 1; $i < $length && ($quarterIndex + $i) < 96; $i++) {
            $slots[$dayIndex][$quarterIndex + $i] = 'covered';
        }
    }

    // Génération d'un tableau exploitable pour l'affichage
   $displayRows = [];
$covered = []; // pour marquer les quarts déjà pris par une diffusion

for ($i = 0; $i < 96; $i++) {
    $row = [
        'hour' => intdiv($i, 4),
        'quarter' => $i % 4,
        'slots' => []
    ];

    for ($day = 0; $day < 7; $day++) {
        // Si ce quart est couvert par une diffusion précédente, on saute
        if (isset($covered[$day][$i])) {
            $row['slots'][$day] = ['type' => 'covered'];
            continue;
        }

        $cell = $slots[$day][$i] ?? null;

        if ($cell instanceof \App\Entity\Diffusion) {
            $duration = $cell->getEmission()->getDuree();
            $length = (int) ceil($duration / 15);

            // Marquer les prochaines cellules comme "couvertes"
            for ($offset = 1; $offset < $length; $offset++) {
                $covered[$day][$i + $offset] = true;
            }

            $row['slots'][$day] = [
                'type' => 'emission',
                'diffusion' => $cell,
                'colspan' => $length
            ];
        } else {
            $row['slots'][$day] = ['type' => 'empty'];
        }
    }

    $displayRows[] = $row;
}



    return $this->render('admin/grille/index.html.twig', [
        'startOfWeek' => $startOfWeekDate,
        'jours' => $jours,
        'displayRows' => $displayRows,
    ]);
}





}
