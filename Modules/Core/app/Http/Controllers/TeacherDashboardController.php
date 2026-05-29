<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Academics\Models\Attendance;
use Modules\Academics\Models\GroupSession;
use Modules\Academics\Models\TeachingGroup;
use Modules\Exams\Models\Exam;
use Modules\Exams\Models\ExamAttempt;
use Modules\Notifications\Support\PortalNotificationData;
use Modules\People\Models\Student;

class TeacherDashboardController extends Controller
{
    public function __invoke(): Response
    {
        [$weekStart, $weekEnd] = $this->currentTeachingWeek();

        return Inertia::render('Admin/Dashboard', [
            'metrics' => $this->metrics($weekStart, $weekEnd),
            'charts' => [
                'attendanceTrend' => $this->attendanceTrend(),
                'weeklySessions' => $this->weeklySessions($weekStart, $weekEnd),
                'examPipeline' => $this->examPipeline(),
                'studentGrowth' => $this->studentGrowth(),
                'groupLoad' => $this->groupLoad(),
            ],
            'upcomingSessions' => GroupSession::query()
                ->with('group')
                ->where('starts_at', '>=', now()->startOfDay())
                ->orderBy('starts_at')
                ->limit(5)
                ->get()
                ->map(fn (GroupSession $session): array => [
                    'id' => $session->id,
                    'title' => $session->title,
                    'starts_at' => $session->starts_at?->toDayDateTimeString(),
                    'group' => $session->group?->name,
                ]),
            'recentNotifications' => DatabaseNotification::query()
                ->with('notifiable')
                ->where('type', PortalNotificationData::type())
                ->latest()
                ->limit(5)
                ->get()
                ->map(function (DatabaseNotification $notification): array {
                    $data = PortalNotificationData::from($notification);

                    return [
                        'id' => $notification->id,
                        'title' => $data['title'],
                        'body' => $data['body'],
                        'student' => $data['student_name'],
                        'recipient_role' => $data['audience'],
                        'recipient_name' => $notification->notifiable?->name,
                        'created_at' => $notification->created_at?->diffForHumans(),
                    ];
                }),
            'quickActions' => [
                ['label' => 'Add Parent', 'href' => route('admin.parents.create')],
                ['label' => 'Add Student', 'href' => route('admin.students.create')],
                ['label' => 'Add Group', 'href' => route('admin.groups.create')],
                ['label' => 'Add Manual Session', 'href' => route('admin.sessions.create')],
                ['label' => 'Add Exam', 'href' => route('admin.exams.create')],
                ['label' => 'Send Notification', 'href' => route('admin.notifications.index')],
            ],
        ]);
    }

    /**
     * @return array<int, array{label: string, value: int, tone: string}>
     */
    private function metrics(CarbonInterface $weekStart, CarbonInterface $weekEnd): array
    {
        return [
            ['label' => 'Total Students', 'value' => Student::query()->count(), 'tone' => 'blue'],
            ['label' => 'Active Students', 'value' => Student::query()->where('is_active', true)->count(), 'tone' => 'mint'],
            ['label' => 'Parents', 'value' => User::query()->where('role', 'parent')->count(), 'tone' => 'yellow'],
            ['label' => 'Active Groups', 'value' => TeachingGroup::query()->where('is_active', true)->count(), 'tone' => 'coral'],
            ['label' => 'Sessions This Week', 'value' => GroupSession::query()->whereBetween('starts_at', [$weekStart, $weekEnd])->count(), 'tone' => 'blue'],
            ['label' => 'Upcoming Exams', 'value' => Exam::query()->where('start_at', '>=', now())->count(), 'tone' => 'mint'],
        ];
    }

    private function attendanceTrend(): array
    {
        $start = now()->subDays(13)->startOfDay();
        $end = now()->endOfDay();
        $statuses = ['present', 'absent', 'late', 'excused'];
        $days = $this->dateBuckets($start, 14);
        $records = Attendance::query()
            ->with('session')
            ->whereHas('session', fn ($query) => $query->whereBetween('starts_at', [$start, $end]))
            ->get();

        $counts = collect($statuses)
            ->mapWithKeys(fn (string $status): array => [$status => array_fill_keys($days->keys()->all(), 0)])
            ->all();

        foreach ($records as $record) {
            $date = $record->session?->starts_at?->toDateString();

            if ($date && isset($counts[$record->status][$date])) {
                $counts[$record->status][$date]++;
            }
        }

        return [
            'labels' => $days->values()->all(),
            'datasets' => collect($statuses)
                ->map(fn (string $status): array => [
                    'label' => str($status)->headline()->toString(),
                    'data' => array_values($counts[$status]),
                ])
                ->values()
                ->all(),
        ];
    }

    private function weeklySessions(CarbonInterface $weekStart, CarbonInterface $weekEnd): array
    {
        $days = $this->dateBuckets($weekStart, 7, 'D M j');
        $counts = array_fill_keys($days->keys()->all(), 0);

        GroupSession::query()
            ->whereBetween('starts_at', [$weekStart, $weekEnd])
            ->get(['starts_at'])
            ->each(function (GroupSession $session) use (&$counts): void {
                $date = $session->starts_at?->toDateString();

                if ($date && array_key_exists($date, $counts)) {
                    $counts[$date]++;
                }
            });

        return [
            'labels' => $days->values()->all(),
            'datasets' => [[
                'label' => 'Sessions',
                'data' => array_values($counts),
            ]],
        ];
    }

    private function examPipeline(): array
    {
        return [
            'labels' => ['Upcoming exams', 'Conducted exams', 'In-progress attempts', 'Submitted attempts'],
            'datasets' => [[
                'label' => 'Exam Pipeline',
                'data' => [
                    Exam::query()->where('start_at', '>=', now())->count(),
                    Exam::query()->where('end_at', '<', now())->count(),
                    ExamAttempt::query()->where('status', 'in_progress')->count(),
                    ExamAttempt::query()->whereIn('status', ['submitted', 'auto_submitted'])->count(),
                ],
            ]],
        ];
    }

    private function studentGrowth(): array
    {
        $start = now()->startOfMonth()->subMonths(5);
        $months = collect(range(0, 5))
            ->mapWithKeys(fn (int $offset): array => [
                $start->copy()->addMonths($offset)->format('Y-m') => $start->copy()->addMonths($offset)->format('M Y'),
            ]);
        $counts = array_fill_keys($months->keys()->all(), 0);

        Student::query()
            ->where('created_at', '>=', $start)
            ->get(['created_at'])
            ->each(function (Student $student) use (&$counts): void {
                $month = $student->created_at?->format('Y-m');

                if ($month && array_key_exists($month, $counts)) {
                    $counts[$month]++;
                }
            });

        return [
            'labels' => $months->values()->all(),
            'datasets' => [[
                'label' => 'New students',
                'data' => array_values($counts),
            ]],
        ];
    }

    private function groupLoad(): array
    {
        $groups = TeachingGroup::query()
            ->withCount('students')
            ->orderByDesc('students_count')
            ->orderBy('name')
            ->limit(5)
            ->get();

        return [
            'labels' => $groups->pluck('name')->all(),
            'datasets' => [[
                'label' => 'Students',
                'data' => $groups->pluck('students_count')->all(),
            ]],
        ];
    }

    /**
     * @return array{0: CarbonInterface, 1: CarbonInterface}
     */
    private function currentTeachingWeek(): array
    {
        $today = now()->startOfDay();
        $daysSinceSaturday = ($today->dayOfWeek - 6 + 7) % 7;
        $weekStart = $today->copy()->subDays($daysSinceSaturday);

        return [$weekStart, $weekStart->copy()->addDays(6)->endOfDay()];
    }

    /**
     * @return Collection<string, string>
     */
    private function dateBuckets(CarbonInterface $start, int $days, string $format = 'M j'): Collection
    {
        return collect(range(0, $days - 1))
            ->mapWithKeys(fn (int $offset): array => [
                $start->copy()->addDays($offset)->toDateString() => $start->copy()->addDays($offset)->format($format),
            ]);
    }
}
