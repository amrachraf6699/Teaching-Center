<?php

namespace Modules\Core\Support;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Modules\Academics\Models\GroupSession;
use Modules\People\Models\Student;

class BuildStudentWeekView
{
    public function build(Student $student): array
    {
        $weekStart = $this->weekStartsAt();
        $weekEnd = $weekStart->addDays(6)->endOfDay();

        $sessions = GroupSession::query()
            ->with('group')
            ->whereIn('teaching_group_id', $student->groups->pluck('id')->all())
            ->whereBetween('starts_at', [$weekStart, $weekEnd])
            ->orderBy('starts_at')
            ->get();

        $attendanceBySession = $student->attendanceRecords->keyBy('teaching_session_id');
        $weekdayOrder = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

        return [
            'starts_at' => $weekStart->format('M j, Y'),
            'ends_at' => $weekStart->addDays(6)->format('M j, Y'),
            'days' => collect(range(0, 6))
                ->map(function (int $offset) use ($weekStart, $sessions, $attendanceBySession): array {
                    $date = $weekStart->addDays($offset);
                    $daySessions = $sessions
                        ->filter(fn (GroupSession $session): bool => $session->starts_at?->isSameDay($date) ?? false)
                        ->values();

                    return [
                        'date' => $date->toDateString(),
                        'day_name' => $date->format('l'),
                        'day_short' => $date->format('D'),
                        'day_number' => $date->format('j'),
                        'sessions' => $daySessions->map(function (GroupSession $session) use ($attendanceBySession): array {
                            $attendance = $attendanceBySession->get($session->id);

                            return [
                                'id' => $session->id,
                                'title' => $session->title,
                                'group' => $session->group?->name,
                                'subject' => $session->group?->subject,
                                'starts_at' => $session->starts_at?->format('g:i A'),
                                'ends_at' => $session->ends_at?->format('g:i A'),
                                'attendance_status' => $attendance?->status,
                                'attendance_notes' => $attendance?->notes,
                            ];
                        })->all(),
                    ];
                })
                ->sortBy(fn (array $day): int => array_search(strtolower($day['day_name']), $weekdayOrder, true))
                ->values()
                ->all(),
        ];
    }

    public function upcomingSessions(Student $student, int $limit = 5): Collection
    {
        return GroupSession::query()
            ->with('group')
            ->whereIn('teaching_group_id', $student->groups->pluck('id')->all())
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->limit($limit)
            ->get();
    }

    private function weekStartsAt(): CarbonImmutable
    {
        $today = CarbonImmutable::now()->startOfDay();
        $offset = ($today->dayOfWeek + 1) % 7;

        return $today->subDays($offset);
    }
}
