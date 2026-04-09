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
        unset($segments);

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
            $visibleDate = $this->findDayInDisplayedWeek($startOfWeek, $slot->getDayOfWeek());

            if ($visibleDate !== null) {
                $startsAt = $this->applyTime($visibleDate, $slot);

                if ($this->weeklyOccurrenceMatchesRule($rule, $slot, $startsAt)) {
                    $occurrences[] = $startsAt;
                }
            }
        }

        if ($slot->isMonthly()) {
            $monthsToCheck = [
                $startOfWeek->modify('first day of last month')->setTime(0, 0, 0),
                $startOfWeek->modify('first day of this month')->setTime(0, 0, 0),
                $startOfWeek->modify('first day of next month')->setTime(0, 0, 0),
            ];

            foreach ($monthsToCheck as $monthCursor) {
                if (!$this->monthMatchesInterval($rule, $monthCursor, $slot->getMonthInterval())) {
                    continue;
                }

                $baseDate = $this->resolveMonthlyOccurrenceDate(
                    (int) $monthCursor->format('Y'),
                    (int) $monthCursor->format('m'),
                    $slot->getDayOfWeek(),
                    $slot->getMonthlyOccurrence()
                );

                if ($baseDate === null) {
                    continue;
                }

                $visibleDate = $baseDate->modify(sprintf('+%d days', $slot->getWeekOffset() * 7));
                $startsAt = $this->applyTime($visibleDate, $slot);

                if ($startsAt < $startOfWeek || $startsAt >= $endOfWeek) {
                    continue;
                }

                if (!$this->dateMatchesRuleWindow($rule, $baseDate)) {
                    continue;
                }

                $key = $startsAt->format('Y-m-d H:i:s');
                $occurrences[$key] = $startsAt;
            }
        }

        return array_values($occurrences);
    }

    private function weeklyOccurrenceMatchesRule(
        ProgrammationRule $rule,
        ProgrammationRuleSlot $slot,
        \DateTimeImmutable $visibleStartsAt
    ): bool {
        $anchorDate = $visibleStartsAt->modify(sprintf('-%d days', $slot->getWeekOffset() * 7));

        return $this->dateMatchesRuleWindow($rule, $anchorDate);
    }

    private function dateMatchesRuleWindow(
        ProgrammationRule $rule,
        \DateTimeImmutable $date
    ): bool {
        $validFrom = $this->toImmutableStartOfDay($rule->getValidFrom());
        $validUntil = $this->toImmutableEndOfDay($rule->getValidUntil());

        if ($validFrom !== null && $date < $validFrom) {
            return false;
        }

        if ($validUntil !== null && $date > $validUntil) {
            return false;
        }

        return true;
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
            return $date->setTime(0, 0, 0);
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

        $anchor = $this->toImmutableStartOfDay($rule->getValidFrom())
            ?? $monthDate->setDate((int) $monthDate->format('Y'), 1, 1)->setTime(0, 0, 0);

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
            if (empty($matchingDays)) {
                return null;
            }

            $lastMatch = end($matchingDays);

            return $lastMatch instanceof \DateTimeImmutable ? $lastMatch : null;
        }

        $index = $monthlyOccurrence - 1;

        return $matchingDays[$index] ?? null;
    }

    private function toImmutableStartOfDay(?\DateTimeInterface $date): ?\DateTimeImmutable
    {
        if ($date === null) {
            return null;
        }

        if ($date instanceof \DateTimeImmutable) {
            return $date->setTime(0, 0, 0);
        }

        if ($date instanceof \DateTime) {
            return \DateTimeImmutable::createFromMutable($date)->setTime(0, 0, 0);
        }

        return null;
    }

    private function toImmutableEndOfDay(?\DateTimeInterface $date): ?\DateTimeImmutable
    {
        if ($date === null) {
            return null;
        }

        if ($date instanceof \DateTimeImmutable) {
            return $date->setTime(23, 59, 59);
        }

        if ($date instanceof \DateTime) {
            return \DateTimeImmutable::createFromMutable($date)->setTime(23, 59, 59);
        }

        return null;
    }
}