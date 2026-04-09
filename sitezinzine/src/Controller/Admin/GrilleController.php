<?php

namespace App\Controller\Admin;

use App\Entity\DiffusionDraft;
use App\Entity\ProgrammationRuleSlot;
use App\Repository\DiffusionDraftRepository;
use App\Repository\EmissionRepository;
use App\Repository\ProgrammationRuleSlotRepository;
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

    #[Route('/assign', name: 'assign', methods: ['POST'])]
    public function assign(
        Request $request,
        ProgrammationRuleSlotRepository $slotRepository,
        EmissionRepository $emissionRepository,
        DiffusionDraftRepository $draftRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $slotId = $request->request->get('slotId');
        $emissionId = $request->request->get('emissionId');
        $startsAt = $request->request->get('startsAt');

        if (!$slotId || !$emissionId || !$startsAt) {
            return $this->json(['error' => 'Paramètres manquants'], 400);
        }

        $slot = $slotRepository->find($slotId);
        $emission = $emissionRepository->find($emissionId);

        if (!$slot || !$emission) {
            return $this->json(['error' => 'Données invalides'], 404);
        }

        $date = new \DateTimeImmutable($startsAt);

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
            'emissionTitle' => $emission->getTitre(),
        ]);
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

        $draft = $draftRepository->findOneBySlotAndHoraire($slot, $date);

        if (!$draft) {
            return $this->json([
                'success' => true,
                'message' => 'Aucun brouillon à supprimer.',
            ]);
        }

        $em->remove($draft);
        $em->flush();

        return $this->json([
            'success' => true,
        ]);
    }
}
