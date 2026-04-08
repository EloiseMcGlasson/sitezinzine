<?php

namespace App\Service;

use App\Entity\ProgrammationRule;
use App\Entity\ProgrammationRuleSlot;
use App\Repository\ProgrammationRuleRepository;

class ProgrammationGridBuilder
{
    public function __construct(
        private ProgrammationRuleRepository $programmationRuleRepository,
    ) {
    }

    public function buildForWeek(\DateTimeImmutable $startOfWeek, \DateTimeImmutable $endOfWeek): array
    {
        $daySegments = array_fill(0, 7, []);

        $rules = $this->programmationRuleRepository->findAll();

        foreach ($rules as $rule) {
            if (!$rule instanceof ProgrammationRule) {
                continue;
            }

            if (!$rule->isCurrentlyValid($startOfWeek)) {
                continue;
            }

            foreach ($rule->getSlots() as $slot) {
                if (!$slot->isActive() || $slot->isDeleted()) {
                    continue;
                }

                $occurrences = $this->generateSlotOccurrencesForWeek($rule, $slot, $startOfWeek, $endOfWeek);

                foreach ($occurrences as $startsAt) {
                    $dayIndex = (int) $startOfWeek->diff($startsAt)->days;

                    if ($dayIndex < 0 || $dayIndex > 6) {
                        continue;
                    }

                    $hour = (int) $startsAt->format('H');
                    $minute = (int) $startsAt->format('i');
                    $startIndex = $hour * 4 + intdiv($minute, 15);

                    if ($startIndex < 0) {
                        $startIndex = 0;
                    }

                    if ($startIndex > 95) {
                        $startIndex = 95;
                    }

                    $daySegments[$dayIndex][] = [
                        'title' => $rule->getCategory()?->getTitre() ?? 'Catégorie inconnue',
                        'duration' => $slot->getDurationMinutes() ?? 15,
                        'startIndex' => $startIndex,
                        'ruleId' => $rule->getId(),
                        'slotId' => $slot->getId(),
                        'broadcastRank' => $slot->getBroadcastRank(),
                        'startsAt' => $startsAt->format('Y-m-d H:i:s'),
                    ];
                }
            }
        }

        foreach ($daySegments as &$segments) {
            usort($segments, static fn(array $a, array $b) => $a['startIndex'] <=> $b['startIndex']);
        }

        return $daySegments;
    }

    /**
     * @return \DateTimeImmutable[]
     */
    private function generateSlotOccurrencesForWeek(
        ProgrammationRule $rule,
        ProgrammationRuleSlot $slot,
        \DateTimeImmutable $startOfWeek,
        \DateTimeImmutable $endOfWeek
    ): array {
        $occurrences = [];

        if ($slot->isWeekly()) {
            $date = $this->findDayInDisplayedWeek($startOfWeek, $slot->getDayOfWeek());
            if ($date !== null) {
                $date = $date->modify(sprintf('+%d days', $slot->getWeekOffset() * 7));
                $startsAt = $this->applyTime($date, $slot);

                if ($startsAt >= $startOfWeek && $startsAt < $endOfWeek) {
                    $occurrences[] = $startsAt;
                }
            }
        }

        if ($slot->isMonthly()) {
            $cursor = $startOfWeek->modify('first day of this month')->setTime(0, 0);

            while ($cursor < $endOfWeek) {
                if ($this->monthMatchesInterval($rule, $cursor, $slot->getMonthInterval())) {
                    $baseDate = $this->resolveMonthlyOccurrenceDate(
                        (int) $cursor->format('Y'),
                        (int) $cursor->format('m'),
                        $slot->getDayOfWeek(),
                        $slot->getMonthlyOccurrence()
                    );

                    if ($baseDate !== null) {
                        $baseDate = $baseDate->modify(sprintf('+%d days', $slot->getWeekOffset() * 7));
                        $startsAt = $this->applyTime($baseDate, $slot);

                        if ($startsAt >= $startOfWeek && $startsAt < $endOfWeek) {
                            $occurrences[] = $startsAt;
                        }
                    }
                }

                $cursor = $cursor->modify('first day of next month')->setTime(0, 0);
            }
        }

        return $occurrences;
    }

    private function findDayInDisplayedWeek(\DateTimeImmutable $startOfWeek, ?int $dayOfWeek): ?\DateTimeImmutable
    {
        if ($dayOfWeek === null) {
            return null;
        }

        for ($i = 0; $i < 7; $i++) {
            $candidate = $startOfWeek->modify(sprintf('+%d days', $i));

            if ((int) $candidate->format('N') === $dayOfWeek) {
                return $candidate;
            }
        }

        return null;
    }

    private function applyTime(\DateTimeImmutable $date, ProgrammationRuleSlot $slot): \DateTimeImmutable
    {
        $startTime = $slot->getStartTime();

        if ($startTime === null) {
            return $date->setTime(0, 0);
        }

        return $date->setTime(
            (int) $startTime->format('H'),
            (int) $startTime->format('i'),
            0
        );
    }

    private function monthMatchesInterval(
        ProgrammationRule $rule,
        \DateTimeImmutable $monthDate,
        int $monthInterval
    ): bool {
        if ($monthInterval <= 1) {
            return true;
        }

        $anchor = $rule->getValidFrom()
            ? $rule->getValidFrom()->setTime(0, 0)
            : $monthDate->setDate((int) $monthDate->format('Y'), 1, 1)->setTime(0, 0);

        $anchorYear = (int) $anchor->format('Y');
        $anchorMonth = (int) $anchor->format('n');

        $currentYear = (int) $monthDate->format('Y');
        $currentMonth = (int) $monthDate->format('n');

        $diffInMonths = (($currentYear - $anchorYear) * 12) + ($currentMonth - $anchorMonth);

        return $diffInMonths >= 0 && $diffInMonths % $monthInterval === 0;
    }

    private function resolveMonthlyOccurrenceDate(
        int $year,
        int $month,
        ?int $dayOfWeek,
        ?int $monthlyOccurrence
    ): ?\DateTimeImmutable {
        if ($dayOfWeek === null || $monthlyOccurrence === null) {
            return null;
        }

        $firstDay = new \DateTimeImmutable(sprintf('%04d-%02d-01 00:00:00', $year, $month));
        $lastDay = $firstDay->modify('last day of this month');

        $matchingDays = [];

        $cursor = $firstDay;
        while ($cursor <= $lastDay) {
            if ((int) $cursor->format('N') === $dayOfWeek) {
                $matchingDays[] = $cursor;
            }

            $cursor = $cursor->modify('+1 day');
        }

        if ($monthlyOccurrence === ProgrammationRuleSlot::MONTHLY_LAST) {
            return !empty($matchingDays) ? end($matchingDays) : null;
        }

        $index = $monthlyOccurrence - 1;

        return $matchingDays[$index] ?? null;
    }
}