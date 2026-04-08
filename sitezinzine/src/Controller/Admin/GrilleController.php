<?php

namespace App\Controller\Admin;

use App\Service\ProgrammationGridBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\ProgrammationRuleSlot;
use App\Repository\EmissionRepository;
use App\Repository\ProgrammationRuleSlotRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

#[Route("/admin/grille", name: 'admin.grille.')]
#[IsGranted("ROLE_ADMIN")]
class GrilleController extends AbstractController
{
    #[Route(
    '/{startOfWeek}',
    name: 'index',
    methods: ['GET'],
    requirements: ['startOfWeek' => '\d{4}-\d{2}-\d{2}'],
    defaults: ['startOfWeek' => null]
)]
    public function index(?string $startOfWeek, ProgrammationGridBuilder $programmationGridBuilder): Response
    {
        $startDate = $startOfWeek
            ? \DateTime::createFromFormat('Y-m-d', $startOfWeek)
            : new \DateTime();

        $startOfWeekDate = (clone $startDate)->modify('this week')->modify('+1 day')->setTime(0, 0, 0);
        $endOfWeekDate = (clone $startOfWeekDate)->modify('+7 days');

        $jours = [];
        for ($i = 0; $i < 7; $i++) {
            $jours[] = (clone $startOfWeekDate)->modify("+$i days");
        }

        $daySegments = $programmationGridBuilder->buildForWeek(
            \DateTimeImmutable::createFromMutable($startOfWeekDate),
            \DateTimeImmutable::createFromMutable($endOfWeekDate)
        );

        return $this->render('admin/grille/index.html.twig', [
            'startOfWeek' => $startOfWeekDate,
            'jours' => $jours,
            'daySegments' => $daySegments,
        ]);
    }

   #[Route('/candidates', name: 'candidates', methods: ['GET'])]
public function candidates(
    Request $request,
    ProgrammationRuleSlotRepository $slotRepository,
    EmissionRepository $emissionRepository
): JsonResponse {
    $slotId = $request->query->get('slotId');

    if (!$slotId) {
        return $this->json(['items' => []], 400);
    }

    $slot = $slotRepository->find($slotId);

    if (!$slot instanceof ProgrammationRuleSlot || $slot->isDeleted() || !$slot->isActive()) {
        return $this->json(['items' => []], 404);
    }

    $rule = $slot->getRule();
    $category = $rule?->getCategory();

    if ($category === null) {
        return $this->json(['items' => []]);
    }

    if ($category->isActive() !== true) {
        return $this->json(['items' => []]);
    }

    if ($category->isSoftDelete() !== false) {
        return $this->json(['items' => []]);
    }

    $emissions = $emissionRepository->findAssignableForCategory($category);

    $items = [];
    foreach ($emissions as $emission) {
        $items[] = [
            'id' => $emission->getId(),
            'title' => $emission->getTitre(),
            'meta' => sprintf('%d min', $emission->getDuree() ?? 0),
        ];
    }

    return $this->json([
        'items' => $items,
    ]);
}
}