<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Academics\Models\GroupSession;

class StudentDashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $request->user()->load([
            'studentProfile.groups',
            'studentProfile.attendanceRecords.session.group',
        ]);

        $student = $request->user()->studentProfile;
        $weekStart = $this->weekStartsAt();
        $weekEnd = $weekStart->addDays(6)->endOfDay();
        $sessions = GroupSession::query()
            ->with('group')
            ->whereIn('teaching_group_id', $student?->groups->pluck('id')->all() ?? [])
            ->whereBetween('starts_at', [$weekStart, $weekEnd])
            ->orderBy('starts_at')
            ->get();
        $attendanceBySession = $student?->attendanceRecords
            ->keyBy('teaching_session_id') ?? collect();
        $weekdayOrder = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        $calendarDays = collect(range(0, 6))
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
            ->all();

        return Inertia::render('Student/Dashboard', [
            'student' => [
                'id' => $student?->id,
                'name' => $student?->name,
                'code' => $student?->code,
                'week' => [
                    'starts_at' => $weekStart->format('M j, Y'),
                    'ends_at' => $weekStart->addDays(6)->format('M j, Y'),
                    'days' => $calendarDays,
                ],
                'groups' => $student?->groups->map(fn ($group): array => [
                    'id' => $group->id,
                    'name' => $group->name,
                    'subject' => $group->subject,
                ])->values()->all() ?? [],
                'attendance' => $student?->attendanceRecords
                    ->sortByDesc('created_at')
                    ->take(10)
                    ->map(fn ($attendance): array => [
                        'id' => $attendance->id,
                        'status' => $attendance->status,
                        'notes' => $attendance->notes,
                        'session' => $attendance->session?->title,
                        'group' => $attendance->session?->group?->name,
                        'starts_at' => $attendance->session?->starts_at?->toDayDateTimeString(),
                    ])->values()->all() ?? [],
            ],
        ]);
    }

    private function weekStartsAt(): CarbonImmutable
    {
        $today = CarbonImmutable::now()->startOfDay();
        $offset = ($today->dayOfWeek + 1) % 7;

        return $today->subDays($offset);
    }
}
