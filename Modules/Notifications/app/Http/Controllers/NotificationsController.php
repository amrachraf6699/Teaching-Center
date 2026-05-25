<?php

namespace Modules\Notifications\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Notifications\Models\ParentNotification;
use Modules\Notifications\Services\ParentNotificationService;
use Modules\People\Models\Student;

class NotificationsController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'student_id' => (string) $request->query('student_id', ''),
            'recipient' => (string) $request->query('recipient', ''),
        ];

        return Inertia::render('Admin/Notifications/Index', [
            'filters' => $filters,
            'students' => Student::query()
                ->with(['parent:id,name,email', 'user:id,name'])
                ->orderBy('name')
                ->get(['id', 'parent_id', 'user_id', 'name', 'code'])
                ->map(fn (Student $student): array => [
                    'value' => (string) $student->id,
                    'label' => sprintf('%s (%s)', $student->name, $student->code),
                    'description' => collect([
                        $student->parent ? 'Parent: '.$student->parent->name : null,
                        $student->user ? 'Student login ready' : 'Student login missing',
                    ])->filter()->join(' · '),
                    'recipient_state' => [
                        'parent' => (bool) $student->parent_id,
                        'student' => (bool) $student->user_id,
                    ],
                ])->values()->all(),
            'recipientOptions' => [
                ['value' => 'parent', 'label' => 'Parent account'],
                ['value' => 'student', 'label' => 'Student account'],
            ],
            'notifications' => ParentNotification::query()
                ->with(['student:id,name,code', 'recipient:id,name', 'parent:id,name'])
                ->when($filters['student_id'] !== '', fn ($query) => $query->where('student_id', $filters['student_id']))
                ->when(in_array($filters['recipient'], ['parent', 'student'], true), fn ($query) => $query->where('recipient_role', $filters['recipient']))
                ->latest()
                ->paginate(20)
                ->withQueryString()
                ->through(fn (ParentNotification $notification): array => [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'body' => $notification->body,
                    'type' => $notification->type,
                    'recipient_role' => $notification->recipient_role,
                    'recipient_name' => $notification->recipient?->name,
                    'student_name' => $notification->student?->name,
                    'student_code' => $notification->student?->code,
                    'created_at' => $notification->created_at?->toDayDateTimeString(),
                ]),
            'indexUrl' => route('admin.notifications.index'),
            'storeUrl' => route('admin.notifications.store'),
        ]);
    }

    public function store(Request $request, ParentNotificationService $notifications): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'recipient' => ['required', 'in:parent,student'],
            'type' => ['nullable', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $student = Student::query()
            ->with(['parent', 'user'])
            ->findOrFail($data['student_id']);

        $notifications->createForAudience(
            $student,
            $data['recipient'],
            $data['type'] ?? 'general',
            $data['title'],
            $data['body'],
        );

        return redirect()
            ->route('admin.notifications.index', [
                'student_id' => $student->id,
                'recipient' => $data['recipient'],
            ])
            ->with('status', 'Notification sent.');
    }
}
