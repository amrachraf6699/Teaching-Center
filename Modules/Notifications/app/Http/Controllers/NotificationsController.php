<?php

namespace Modules\Notifications\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Notifications\Services\PortalNotificationService;
use Modules\Notifications\Support\PortalNotificationData;
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
            'notifications' => DatabaseNotification::query()
                ->with('notifiable')
                ->where('type', PortalNotificationData::type())
                ->when($filters['student_id'] !== '', fn ($query) => $query->where('data->student_id', (int) $filters['student_id']))
                ->when(in_array($filters['recipient'], ['parent', 'student'], true), fn ($query) => $query->where('data->audience', $filters['recipient']))
                ->latest()
                ->paginate(20)
                ->withQueryString()
                ->through(function (DatabaseNotification $notification): array {
                    $data = PortalNotificationData::from($notification);

                    return [
                    'id' => $notification->id,
                    'title' => $data['title'],
                    'body' => $data['body'],
                    'type' => $data['type'],
                    'recipient_role' => $data['audience'],
                    'recipient_name' => $notification->notifiable?->name,
                    'student_name' => $data['student_name'],
                    'student_code' => $data['student_code'],
                    'created_at' => $notification->created_at?->toDayDateTimeString(),
                    ];
                }),
            'indexUrl' => route('admin.notifications.index'),
            'storeUrl' => route('admin.notifications.store'),
        ]);
    }

    public function store(Request $request, PortalNotificationService $notifications): RedirectResponse
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
            ->with('status', __('flash.notification.sent'));
    }
}
