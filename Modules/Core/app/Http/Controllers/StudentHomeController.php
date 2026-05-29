<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Core\Support\BuildStudentWeekView;
use Modules\Exams\Services\StudentExamService;
use Modules\Notifications\Support\PortalNotificationData;

class StudentHomeController extends Controller
{
    public function __invoke(Request $request, BuildStudentWeekView $weekView, StudentExamService $examService): Response
    {
        $request->user()->load([
            'studentProfile.groups',
            'studentProfile.attendanceRecords.session.group',
            'studentNotifications',
        ]);

        $student = $request->user()->studentProfile;
        abort_unless($student, 403);

        $examBuckets = $examService->indexDataForStudent($student);

        return Inertia::render('Student/Home', [
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'code' => $student->code,
                'phone' => $student->phone,
                'date_of_birth' => $student->date_of_birth?->toFormattedDateString(),
                'notes' => $student->notes,
                'groups' => $student->groups->map(fn ($group): array => [
                    'id' => $group->id,
                    'name' => $group->name,
                    'subject' => $group->subject,
                ])->values()->all(),
                'attendance' => $student->attendanceRecords
                    ->sortByDesc('created_at')
                    ->take(6)
                    ->map(fn ($attendance): array => [
                        'id' => $attendance->id,
                        'status' => $attendance->status,
                        'notes' => $attendance->notes,
                        'session' => $attendance->session?->title,
                        'group' => $attendance->session?->group?->name,
                        'starts_at' => $attendance->session?->starts_at?->toDayDateTimeString(),
                    ])->values()->all(),
                'notifications' => $request->user()->studentNotifications
                    ->sortByDesc('created_at')
                    ->take(6)
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
                'upcoming_sessions' => $weekView->upcomingSessions($student)->map(fn ($session): array => [
                    'id' => $session->id,
                    'title' => $session->title,
                    'group' => $session->group?->name,
                    'subject' => $session->group?->subject,
                    'starts_at' => $session->starts_at?->toDayDateTimeString(),
                ])->all(),
                'week' => $weekView->build($student),
                'upcoming_exams' => collect($examBuckets['upcoming'])->take(4)->values()->all(),
                'conducted_exams' => collect($examBuckets['conducted'])->take(4)->values()->all(),
            ],
        ]);
    }
}
