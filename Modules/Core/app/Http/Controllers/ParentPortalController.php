<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Core\Support\BuildStudentWeekView;
use Modules\Exams\Models\ExamResult;
use Modules\Exams\Services\StudentExamService;
use Modules\Notifications\Support\PortalNotificationData;

class ParentPortalController extends Controller
{
    public function __invoke(Request $request, BuildStudentWeekView $weekView, StudentExamService $examService): Response
    {
        $children = $request->user()
            ->children()
            ->with([
                'attendanceRecords.session.group',
                'groups',
                'user.studentNotifications',
            ])
            ->get()
            ->map(function ($child) use ($weekView, $examService): array {
                $examBuckets = $examService->indexDataForStudent($child);
                $latestExam = ExamResult::query()
                    ->with('exam')
                    ->where('student_id', $child->id)
                    ->latest()
                    ->first();
                $recentAttendance = $child->attendanceRecords
                    ->sortByDesc('created_at')
                    ->take(6)
                    ->map(fn ($attendance): array => [
                        'id' => $attendance->id,
                        'status' => $attendance->status,
                        'notes' => $attendance->notes,
                        'session' => $attendance->session?->title,
                        'group' => $attendance->session?->group?->name,
                        'starts_at' => $attendance->session?->starts_at?->toDayDateTimeString(),
                    ])->values()->all();
                $latestAttendance = $recentAttendance[0]['status'] ?? null;

                return [
                    'id' => $child->id,
                    'name' => $child->name,
                    'code' => $child->code,
                    'phone' => $child->phone,
                    'group_count' => $child->groups->count(),
                    'groups' => $child->groups->map(fn ($group): array => [
                        'id' => $group->id,
                        'name' => $group->name,
                        'subject' => $group->subject,
                    ])->values()->all(),
                    'week' => $weekView->build($child),
                    'upcoming_sessions' => $weekView->upcomingSessions($child)->map(fn ($session): array => [
                        'id' => $session->id,
                        'title' => $session->title,
                        'group' => $session->group?->name,
                        'subject' => $session->group?->subject,
                        'starts_at' => $session->starts_at?->toDayDateTimeString(),
                    ])->values()->all(),
                    'recent_attendance' => $recentAttendance,
                    'last_attendance' => $latestAttendance,
                    'upcoming_exams' => collect($examBuckets['upcoming'])
                        ->take(4)
                        ->map(fn (array $exam): array => $this->parentExamPayload($exam))
                        ->values()
                        ->all(),
                    'conducted_exams' => collect($examBuckets['conducted'])
                        ->take(4)
                        ->map(fn (array $exam): array => $this->parentExamPayload($exam))
                        ->values()
                        ->all(),
                    'latest_exam_percentage' => $latestExam?->percentage(),
                    'latest_exam_title' => $latestExam?->exam?->title,
                    'recent_notifications' => ($child->user?->studentNotifications ?? collect())
                        ->sortByDesc('created_at')
                        ->take(4)
                        ->map(function (DatabaseNotification $notification): array {
                            $data = PortalNotificationData::from($notification);

                            return [
                                'id' => $notification->id,
                                'type' => $data['type'],
                                'title' => $data['title'],
                                'body' => $data['body'],
                                'created_at' => $notification->created_at?->toDayDateTimeString(),
                            ];
                        })->values()->all(),
                    'pdf_url' => route('parent.child.pdf', $child->id),
                ];
            });

        return Inertia::render('Parent/Dashboard', [
            'children' => $children,
        ]);
    }

    /**
     * @param  array<string, mixed>  $exam
     * @return array<string, mixed>
     */
    private function parentExamPayload(array $exam): array
    {
        unset($exam['show_url'], $exam['attempt_url']);

        return $exam;
    }
}
