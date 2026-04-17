<?php

namespace App\Controller\Admin;

use App\Entity\DiffusionDraft;
use App\Entity\Emission;
use App\Entity\ProgrammationRuleSlot;
use App\Repository\DiffusionDraftRepository;
use App\Repository\EmissionRepository;
use App\Repository\ProgrammationRuleSlotRepository;
use App\Service\GridAssignmentService;
use App\Service\LiveEmissionCreator;
use App\Service\ProgrammationGridBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/admin/grille', name: 'admin.grille.')]
#[IsGranted('ROLE_ADMIN')]
class GrilleController extends AbstractController
{
    #[Route('', name: 'index_current', methods: ['GET'])]
    public function indexCurrent(
        ProgrammationGridBuilder $programmationGridBuilder,
        DiffusionDraftRepository $draftRepository
    ): Response {
        return $this->renderGrid(null, $programmationGridBuilder, $draftRepository);
    }

    #[Route('/{startOfWeek}', name: 'index', methods: ['GET'], requirements: ['startOfWeek' => '\d{4}-\d{2}-\d{2}'])]
    public function index(
        string $startOfWeek,
        ProgrammationGridBuilder $programmationGridBuilder,
        DiffusionDraftRepository $draftRepository
    ): Response {
        return $this->renderGrid($startOfWeek, $programmationGridBuilder, $draftRepository);
    }

    private function renderGrid(
        ?string $startOfWeek,
        ProgrammationGridBuilder $programmationGridBuilder,
        DiffusionDraftRepository $draftRepository
    ): Response {
        $startDate = $startOfWeek
            ? \DateTime::createFromFormat('Y-m-d', $startOfWeek)
            : new \DateTime();

        if (!$startDate) {
            throw $this->createNotFoundException('Date de semaine invalide.');
        }

        $startOfWeekDate = (clone $startDate)
            ->modify('this week')
            ->modify('+1 day')
            ->setTime(0, 0, 0);

        $endOfWeekDate = (clone $startOfWeekDate)->modify('+7 days');

        $jours = [];
        for ($i = 0; $i < 7; $i++) {
            $jours[] = (clone $startOfWeekDate)->modify("+$i days");
        }

        $startImmutable = \DateTimeImmutable::createFromMutable($startOfWeekDate);
        $endImmutable = \DateTimeImmutable::createFromMutable($endOfWeekDate);

        $daySegments = $programmationGridBuilder->buildForWeek($startImmutable, $endImmutable);
        $drafts = $draftRepository->findByWeek($startImmutable, $endImmutable);

        $draftIndex = [];
        foreach ($drafts as $draft) {
            $key = $this->buildDraftKey(
                $draft->getSlot()?->getId(),
                $draft->getHoraireDiffusion()
            );

            if ($key !== null) {
                $draftIndex[$key] = $draft;
            }
        }

        foreach ($daySegments as &$segments) {
            foreach ($segments as &$seg) {
                $seg['assigned'] = false;
                $seg['emissionId'] = null;
                $seg['emissionTitle'] = null;
                $seg['categoryTitle'] = $seg['title'] ?? 'Catégorie inconnue';
                $seg['displayTitle'] = $seg['title'] ?? 'Créneau';

                $slotId = $seg['slotId'] ?? null;
                $startsAt = $seg['startsAt'] ?? null;

                if (!$slotId || !$startsAt) {
                    continue;
                }

                $startsAtDate = $startsAt instanceof \DateTimeInterface
                    ? \DateTimeImmutable::createFromInterface($startsAt)
                    : new \DateTimeImmutable((string) $startsAt);

                $key = $this->buildDraftKey((int) $slotId, $startsAtDate);

                if (!isset($draftIndex[$key])) {
                    continue;
                }

                /** @var DiffusionDraft $draft */
                $draft = $draftIndex[$key];
                $emission = $draft->getEmission();

                if ($emission !== null) {
                    $seg['assigned'] = true;
                    $seg['emissionId'] = $emission->getId();
                    $seg['emissionTitle'] = $emission->getTitre();
                    $seg['displayTitle'] = $emission->getTitre();
                }
            }
        }
        unset($segments, $seg);

        return $this->render('admin/grille/index.html.twig', [
            'startOfWeek' => $startOfWeekDate,
            'jours' => $jours,
            'daySegments' => $daySegments,
        ]);
    }

    private function buildDraftKey(?int $slotId, ?\DateTimeInterface $horaire): ?string
    {
        if ($slotId === null || $horaire === null) {
            return null;
        }

        return $slotId . '|' . $horaire->format('Y-m-d H:i:s');
    }

    #[Route('/candidates', name: 'candidates', methods: ['GET'])]
    public function candidates(
        Request $request,
        ProgrammationRuleSlotRepository $slotRepository,
        EmissionRepository $emissionRepository
    ): JsonResponse {
        $slotId = $request->query->getInt('slotId');
        $startsAt = $request->query->get('startsAt');

        if ($slotId <= 0 || !$startsAt) {
            return $this->json(['items' => []], 400);
        }

        $slot = $slotRepository->find($slotId);

        if (!$slot instanceof ProgrammationRuleSlot || $slot->isDeleted() || !$slot->isActive()) {
            return $this->json(['items' => []], 404);
        }

        try {
            $selectedDate = new \DateTimeImmutable($startsAt);
        } catch (\Exception) {
            return $this->json(['items' => []], 400);
        }

        $rule = $slot->getRule();
        $category = $rule?->getCategory();
        $broadcastRank = $slot->getBroadcastRank();

        if ($category === null) {
            return $this->json(['items' => []]);
        }

        if ($category->isActive() !== true) {
            return $this->json(['items' => []]);
        }

        if ($category->isSoftDelete() !== false) {
            return $this->json(['items' => []]);
        }

        if ($broadcastRank === null || $broadcastRank < 1) {
            return $this->json(['items' => []], 400);
        }

        $emissions = [];

        if ($broadcastRank === 1) {
            $latestEmissions = $emissionRepository->findLatestFirstPassCandidatesByCategory($category, 20);

            $emissions = array_values(array_filter(
                $latestEmissions,
                static fn(Emission $emission): bool =>
                $emission->getDiffusions()->isEmpty()
                    && !$emission->isAutoGenerated()
            ));

            $emissions = array_slice($emissions, 0, 10);
        }

        $autoGeneratedForCurrentSlot = $emissionRepository->findAutoGeneratedForSlotAndStartsAt(
            $slot,
            $selectedDate
        );

        if ($autoGeneratedForCurrentSlot instanceof Emission) {
            $alreadyPresent = false;

            foreach ($emissions as $candidateEmission) {
                if ($candidateEmission->getId() === $autoGeneratedForCurrentSlot->getId()) {
                    $alreadyPresent = true;
                    break;
                }
            }

            if (!$alreadyPresent) {
                array_unshift($emissions, $autoGeneratedForCurrentSlot);
            }
        }

        $items = [];
        foreach ($emissions as $emission) {
            $items[] = [
                'id' => $emission->getId(),
                'title' => $emission->getTitre(),
                'meta' => sprintf(
                    '%s • %d min',
                    $emission->getDatepub()?->format('d/m/Y') ?? 'date inconnue',
                    $emission->getDuree() ?? 0
                ),
                'isAutoGenerated' => $emission->isAutoGenerated(),
            ];
        }

        return $this->json([
            'items' => $items,
        ]);
    }

    #[Route('/assign', name: 'assign', methods: ['POST'])]
    public function assign(
        Request $request,
        ProgrammationRuleSlotRepository $slotRepository,
        EmissionRepository $emissionRepository,
        GridAssignmentService $gridAssignmentService
    ): JsonResponse {
        $slotId = $request->request->get('slotId');
        $emissionId = $request->request->get('emissionId');
        $startsAt = $request->request->get('startsAt');

        if (!$slotId || !$emissionId || !$startsAt) {
            return $this->json(['error' => 'Paramètres manquants'], 400);
        }

        $slot = $slotRepository->find($slotId);
        $emission = $emissionRepository->find($emissionId);

        if (!$slot instanceof ProgrammationRuleSlot || !$emission instanceof Emission) {
            return $this->json(['error' => 'Données invalides'], 404);
        }

        try {
            $selectedDate = new \DateTimeImmutable($startsAt);
        } catch (\Exception) {
            return $this->json(['error' => 'Date invalide'], 400);
        }

        try {
            $propagated = $gridAssignmentService->assign($slot, $emission, $selectedDate);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        return $this->json([
            'success' => true,
            'emissionTitle' => $emission->getTitre(),
            'propagated' => $propagated,
        ]);
    }

    #[Route('/create-live', name: 'create_live', methods: ['POST'])]
    public function createLive(
        Request $request,
        ProgrammationRuleSlotRepository $slotRepository,
        EmissionRepository $emissionRepository,
        LiveEmissionCreator $liveCreator,
        DiffusionDraftRepository $draftRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $slotId = $request->request->get('slotId');
        $startsAt = $request->request->get('startsAt');

        if (!$slotId || !$startsAt) {
            return $this->json([
                'success' => false,
                'error' => 'Paramètres manquants',
            ], 400);
        }

        $slot = $slotRepository->find($slotId);

        if (!$slot instanceof ProgrammationRuleSlot || $slot->isDeleted() || !$slot->isActive()) {
            return $this->json([
                'success' => false,
                'error' => 'Créneau invalide',
            ], 404);
        }

        try {
            $date = new \DateTimeImmutable($startsAt);
        } catch (\Exception) {
            return $this->json([
                'success' => false,
                'error' => 'Date invalide',
            ], 400);
        }

        $existingAutoGenerated = $emissionRepository->findAutoGeneratedForSlotAndStartsAt(
            $slot,
            $date
        );

        if ($existingAutoGenerated instanceof Emission) {
            return $this->json([
                'success' => false,
                'error' => 'Une fiche de direct existe déjà pour ce créneau.',
            ], 409);
        }

        try {
            $emission = $liveCreator->createFromSlot($slot, $date);

            if ($slot->getBroadcastRank() === 1) {
                $rule = $slot->getRule();

                if ($rule === null) {
                    return $this->json([
                        'success' => false,
                        'error' => 'Règle introuvable',
                    ], 404);
                }

                foreach ($rule->getSlots() as $relatedSlot) {
                    if (!$relatedSlot instanceof ProgrammationRuleSlot) {
                        continue;
                    }

                    if (!$relatedSlot->isActive() || $relatedSlot->isDeleted()) {
                        continue;
                    }

                    $relatedStartsAt = $this->computeStartsAtFromAnchor($date, $relatedSlot);

                    $draft = $draftRepository->findOneBySlotAndHoraire($relatedSlot, $relatedStartsAt);

                    if (!$draft) {
                        $draft = new DiffusionDraft();
                        $draft->setSlot($relatedSlot);
                        $draft->setHoraireDiffusion($relatedStartsAt);
                    }

                    $draft->setEmission($emission);
                    $draft->setNombreDiffusion($relatedSlot->getBroadcastRank());

                    $em->persist($draft);
                }

                $em->flush();

                return $this->json([
                    'success' => true,
                    'emissionId' => $emission->getId(),
                    'emissionTitle' => $emission->getTitre(),
                    'propagated' => true,
                ]);
            }

            $draft = $draftRepository->findOneBySlotAndHoraire($slot, $date);

            if (!$draft) {
                $draft = new DiffusionDraft();
                $draft->setSlot($slot);
                $draft->setHoraireDiffusion($date);
            }

            $draft->setEmission($emission);
            $draft->setNombreDiffusion($slot->getBroadcastRank());

            $em->persist($draft);
            $em->flush();

            return $this->json([
                'success' => true,
                'emissionId' => $emission->getId(),
                'emissionTitle' => $emission->getTitre(),
                'propagated' => false,
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/remove', name: 'remove', methods: ['POST'])]
    public function remove(
        Request $request,
        ProgrammationRuleSlotRepository $slotRepository,
        DiffusionDraftRepository $draftRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $slotId = $request->request->get('slotId');
        $startsAt = $request->request->get('startsAt');

        if (!$slotId || !$startsAt) {
            return $this->json(['error' => 'Paramètres manquants'], 400);
        }

        $slot = $slotRepository->find($slotId);

        if (!$slot instanceof ProgrammationRuleSlot || $slot->isDeleted() || !$slot->isActive()) {
            return $this->json(['error' => 'Créneau invalide'], 404);
        }

        try {
            $date = new \DateTimeImmutable($startsAt);
        } catch (\Exception) {
            return $this->json(['error' => 'Date invalide'], 400);
        }

        // Si on retire depuis la 1re diffusion, on retire toute la propagation
        if ($slot->getBroadcastRank() === 1) {
            $rule = $slot->getRule();

            if ($rule === null) {
                return $this->json(['error' => 'Règle introuvable'], 404);
            }

            foreach ($rule->getSlots() as $relatedSlot) {
                if (!$relatedSlot instanceof ProgrammationRuleSlot) {
                    continue;
                }

                if (!$relatedSlot->isActive() || $relatedSlot->isDeleted()) {
                    continue;
                }

                $relatedStartsAt = $this->computeStartsAtFromAnchor($date, $relatedSlot);
                $draft = $draftRepository->findOneBySlotAndHoraire($relatedSlot, $relatedStartsAt);

                if ($draft instanceof DiffusionDraft) {
                    $em->remove($draft);
                }
            }

            $em->flush();

            return $this->json([
                'success' => true,
                'propagated' => true,
            ]);
        }

        // Sinon : suppression du seul slot cliqué
        $draft = $draftRepository->findOneBySlotAndHoraire($slot, $date);

        if (!$draft) {
            return $this->json([
                'success' => true,
                'message' => 'Aucun brouillon à supprimer.',
                'propagated' => false,
            ]);
        }

        $em->remove($draft);
        $em->flush();

        return $this->json([
            'success' => true,
            'propagated' => false,
        ]);
    }

    private function computeStartsAtFromAnchor(
        \DateTimeImmutable $anchorDate,
        ProgrammationRuleSlot $slot
    ): \DateTimeImmutable {
        $anchorWeekStart = $this->getRadioWeekStart($anchorDate);

        $targetDate = $anchorWeekStart
            ->modify(sprintf('+%d days', $this->radioDayIndexFromDayOfWeek($slot->getDayOfWeek())))
            ->modify(sprintf('+%d days', $slot->getWeekOffset() * 7));

        $startTime = $slot->getStartTime();

        if ($startTime === null) {
            return $targetDate->setTime(0, 0, 0);
        }

        return $targetDate->setTime(
            (int) $startTime->format('H'),
            (int) $startTime->format('i'),
            0
        );
    }

    private function getRadioWeekStart(\DateTimeImmutable $date): \DateTimeImmutable
    {
        $midnight = $date->setTime(0, 0, 0);
        $dayOfWeek = (int) $midnight->format('N'); // 1=lundi ... 7=dimanche

        return match ($dayOfWeek) {
            2 => $midnight, // mardi
            3 => $midnight->modify('-1 day'),
            4 => $midnight->modify('-2 days'),
            5 => $midnight->modify('-3 days'),
            6 => $midnight->modify('-4 days'),
            7 => $midnight->modify('-5 days'),
            1 => $midnight->modify('-6 days'), // lundi => mardi précédent
            default => $midnight,
        };
    }

    private function radioDayIndexFromDayOfWeek(?int $dayOfWeek): int
    {
        return match ($dayOfWeek) {
            2 => 0, // mardi
            3 => 1, // mercredi
            4 => 2, // jeudi
            5 => 3, // vendredi
            6 => 4, // samedi
            7 => 5, // dimanche
            1 => 6, // lundi
            default => 0,
        };
    }
}
