<?php

namespace Modules\Academics\Actions;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Modules\Academics\Models\GroupSession;
use Modules\Academics\Models\Timetable;
use Modules\Academics\Models\TimetableEntry;

class GenerateSessionsFromTimetables
{
    public function handle(?Timetable $timetable = null, CarbonInterface|string|null $windowStart = null, int $days = 7): void
    {
        $startDate = $windowStart instanceof CarbonInterface
            ? CarbonImmutable::instance($windowStart)->startOfDay()
            : CarbonImmutable::parse($windowStart ?? now())->startOfDay();

        $endDate = $startDate->addDays(max(1, $days) - 1)->endOfDay();
        $query = Timetable::query()->with(['group', 'entries']);

        if ($timetable) {
            $query->whereKey($timetable->getKey());
        }

        $query->get()->each(function (Timetable $currentTimetable) use ($startDate, $endDate): void {
            $this->syncTimetable($currentTimetable, $startDate, $endDate);
        });
    }

    private function syncTimetable(Timetable $timetable, CarbonImmutable $startDate, CarbonImmutable $endDate): void
    {
        foreach ($timetable->entries as $entry) {
            $this->generateEntrySessions($timetable, $entry, $startDate, $endDate);
        }
    }

    private function generateEntrySessions(Timetable $timetable, TimetableEntry $entry, CarbonImmutable $startDate, CarbonImmutable $endDate): void
    {
        for ($date = $startDate; $date->lte($endDate); $date = $date->addDay()) {
            if ($date->englishDayOfWeek !== ucfirst($entry->day_of_week)) {
                continue;
            }

            $startsAt = CarbonImmutable::parse($date->format('Y-m-d').' '.$entry->starts_at);
            $endsAt = CarbonImmutable::parse($date->format('Y-m-d').' '.$entry->ends_at);
            $session = GroupSession::query()
                ->where('timetable_entry_id', $entry->id)
                ->whereDate('session_date', $date->toDateString())
                ->first()
                ?? new GroupSession([
                    'timetable_entry_id' => $entry->id,
                    'session_date' => $date->toDateString(),
                ]);

            if ($session->exists && ($session->attendanceRecords()->exists() || $session->starts_at?->lt(now()))) {
                continue;
            }

            $session->fill([
                'teaching_group_id' => $timetable->teaching_group_id,
                'source_type' => 'timetable',
                'timetable_entry_id' => $entry->id,
                'title' => trim(($timetable->group?->name ?? 'Group').' Session'),
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'session_date' => $date->toDateString(),
                'notes' => $session->notes,
            ]);
            $session->save();
        }
    }
}
