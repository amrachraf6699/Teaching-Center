<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Academics\Models\GroupSession;
use Modules\Academics\Models\TeachingGroup;
use Modules\Exams\Models\Exam;
use Modules\Notifications\Models\ParentNotification;
use Modules\People\Models\Student;

class TeacherDashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'metrics' => [
                ['label' => 'Students', 'value' => Student::count(), 'tone' => 'blue'],
                ['label' => 'Parents', 'value' => User::query()->where('role', 'parent')->count(), 'tone' => 'mint'],
                ['label' => 'Groups', 'value' => TeachingGroup::count(), 'tone' => 'yellow'],
                ['label' => 'Sessions', 'value' => GroupSession::count(), 'tone' => 'coral'],
                ['label' => 'Exams', 'value' => Exam::count(), 'tone' => 'blue'],
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
            'recentNotifications' => ParentNotification::query()
                ->with(['student', 'recipient'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (ParentNotification $notification): array => [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'body' => $notification->body,
                    'student' => $notification->student?->name,
                    'recipient_role' => $notification->recipient_role,
                    'recipient_name' => $notification->recipient?->name,
                    'created_at' => $notification->created_at?->diffForHumans(),
                ]),
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
}
