<?php

namespace App\Service;

use App\Entity\DiffusionDraft;
use App\Entity\Emission;
use App\Entity\ProgrammationRuleSlot;
use App\Repository\DiffusionDraftRepository;
use Doctrine\ORM\EntityManagerInterface;

class GridAssignmentService
{
    public function __construct(
        private readonly DiffusionDraftRepository $draftRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function assign(ProgrammationRuleSlot $slot, Emission $emission, \DateTimeImmutable $selectedDate): bool
    {
        $rule = $slot->getRule();

        if ($rule === null) {
            throw new \RuntimeException('Règle introuvable.');
        }

        if ($slot->getBroadcastRank() === 1) {
            $anchorDate = $selectedDate;

            foreach ($rule->getSlots() as $relatedSlot) {
                if (!$relatedSlot instanceof ProgrammationRuleSlot) {
                    continue;
                }

                if (!$relatedSlot->isActive() || $relatedSlot->isDeleted()) {
                    continue;
                }

                $relatedStartsAt = $this->computeStartsAtFromAnchor($anchorDate, $relatedSlot);
                $duration = $this->resolveDurationMinutes($relatedSlot, $emission);

                $draft = $this->draftRepository->findOneBySlotAndHoraire($relatedSlot, $relatedStartsAt);

                if (!$draft) {
                    $draft = new DiffusionDraft();
                }

                $draft
                    ->setSlot($relatedSlot)
                    ->setSchedule($relatedStartsAt, $duration)
                    ->setEmission($emission)
                    ->setNombreDiffusion($relatedSlot->getBroadcastRank())
                    ->setDraftType(DiffusionDraft::TYPE_REGULAR);

                $this->em->persist($draft);
            }

            $this->em->flush();

            return true;
        }

        $duration = $this->resolveDurationMinutes($slot, $emission);
        $draft = $this->draftRepository->findOneBySlotAndHoraire($slot, $selectedDate);

        if (!$draft) {
            $draft = new DiffusionDraft();
        }

        $draft
            ->setSlot($slot)
            ->setSchedule($selectedDate, $duration)
            ->setEmission($emission)
            ->setNombreDiffusion($slot->getBroadcastRank())
            ->setDraftType(DiffusionDraft::TYPE_REGULAR);

        $this->em->persist($draft);
        $this->em->flush();

        return false;
    }

    private function resolveDurationMinutes(ProgrammationRuleSlot $slot, Emission $emission): int
    {
        $slotDuration = $slot->getDurationMinutes();
        if (\is_int($slotDuration) && $slotDuration > 0) {
            return $slotDuration;
        }

        $emissionDuration = $emission->getDuree();
        if (\is_int($emissionDuration) && $emissionDuration > 0) {
            return $emissionDuration;
        }

        return 15;
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
        $dayOfWeek = (int) $midnight->format('N');

        return match ($dayOfWeek) {
            2 => $midnight,
            3 => $midnight->modify('-1 day'),
            4 => $midnight->modify('-2 days'),
            5 => $midnight->modify('-3 days'),
            6 => $midnight->modify('-4 days'),
            7 => $midnight->modify('-5 days'),
            1 => $midnight->modify('-6 days'),
            default => $midnight,
        };
    }

    private function radioDayIndexFromDayOfWeek(?int $dayOfWeek): int
    {
        return match ($dayOfWeek) {
            2 => 0,
            3 => 1,
            4 => 2,
            5 => 3,
            6 => 4,
            7 => 5,
            1 => 6,
            default => 0,
        };
    }
}