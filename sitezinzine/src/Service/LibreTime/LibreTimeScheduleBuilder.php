<?php

namespace App\Service\LibreTime;

class LibreTimeScheduleBuilder
{
    public function build(array $rawData): array
    {
        $schedule = [];

        foreach ($rawData as $day => $programs) {
            $schedule[$day] = [];

            foreach ($programs as $program) {
                $start = !empty($program['start_timestamp'])
                    ? new \DateTimeImmutable($program['start_timestamp'])
                    : null;

                $end = !empty($program['end_timestamp'])
                    ? new \DateTimeImmutable($program['end_timestamp'])
                    : null;

                $schedule[$day][] = [
                    'name' => html_entity_decode($program['name'] ?? '', ENT_QUOTES | ENT_HTML5),
                    'description' => html_entity_decode($program['description'] ?? '', ENT_QUOTES | ENT_HTML5),
                    'start' => $start,
                    'end' => $end,
                    'duration' => ($start && $end)
                        ? $start->diff($end)
                        : null,
                ];
            }

            usort($schedule[$day], fn ($a, $b) =>
                ($a['start']?->getTimestamp() ?? 0)
                <=>
                ($b['start']?->getTimestamp() ?? 0)
            );
        }

        return $schedule;
    }
}