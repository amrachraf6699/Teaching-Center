<?php

namespace Modules\People\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Academics\Models\TeachingGroup;
use Modules\Notifications\Support\PortalNotificationData;
use Modules\People\Models\Student;

class StudentController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $this->filters($request);

        return Inertia::render('Admin/Students/Index', [
            'students' => $this->filteredIndexQuery($search)
                ->latest()
                ->paginate(20)
                ->withQueryString()
                ->through(fn (Student $student): array => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'code' => $student->code,
                    'phone' => $student->phone,
                    'is_active' => $student->is_active,
                    'toggle_status_url' => route('admin.students.toggle-status', $student),
                    'parent' => $student->parent ? [
                        'id' => $student->parent->id,
                        'name' => $student->parent->name,
                        'email' => $student->parent->email,
                    ] : null,
                    'groups' => $student->groups->map(fn (TeachingGroup $group): array => [
                        'id' => $group->id,
                        'name' => $group->name,
                    ])->values()->all(),
                    'show_url' => route('admin.students.show', $student),
                    'edit_url' => route('admin.students.edit', $student),
                    'delete_url' => route('admin.students.destroy', $student),
                    'created_at' => $student->created_at?->toFormattedDateString(),
                ]),
            'filters' => $search,
            'parentOptions' => $this->parentOptions()
                ->map(fn (User $parent): array => [
                    'value' => (string) $parent->id,
                    'label' => $parent->name,
                    'description' => $parent->email,
                ])->values()->all(),
            'groupOptions' => TeachingGroup::query()
                ->orderBy('name')
                ->get(['id', 'name', 'subject'])
                ->map(fn (TeachingGroup $group): array => [
                    'value' => (string) $group->id,
                    'label' => $group->name,
                    'description' => $group->subject,
                ])->values()->all(),
            'indexUrl' => route('admin.students.index'),
            'createUrl' => route('admin.students.create'),
            'exportUrls' => [
                'csv' => route('admin.students.export', 'csv'),
                'pdf' => route('admin.students.export', 'pdf'),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Students/Create', [
            'parents' => $this->parentOptions(),
            'action' => route('admin.students.store'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'parent_id' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $parent = User::query()->whereKey($data['parent_id'])->where('role', 'parent')->firstOrFail();
        $data['parent_id'] = $parent->id;

        $password = $data['password'] ?? null;
        unset($data['password']);

        $student = Student::create($data);
        $this->syncStudentPassword($student, $password);

        return redirect()->route('admin.students.index')->with('status', 'Student created.');
    }

    public function show(Student $student): Response
    {
        $student->load([
            'parent',
            'groups.sessions.attendanceRecords',
            'attendanceRecords.session.group',
            'examResults.exam.group',
            'user',
            'user.notifications',
        ]);

        return Inertia::render('Admin/Students/Show', [
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'code' => $student->code,
                'phone' => $student->phone,
                'date_of_birth' => $student->date_of_birth?->toFormattedDateString(),
                'notes' => $student->notes,
                'is_active' => $student->is_active,
                'created_at' => $student->created_at?->toDayDateTimeString(),
                'student_login' => [
                    'code' => $student->code,
                    'email' => $student->user?->email,
                    'ready' => (bool) $student->user_id,
                ],
                'edit_url' => route('admin.students.edit', $student),
                'index_url' => route('admin.students.index'),
                'parent' => $student->parent ? [
                    'id' => $student->parent->id,
                    'name' => $student->parent->name,
                    'email' => $student->parent->email,
                    'show_url' => route('admin.parents.show', $student->parent),
                ] : null,
                'groups' => $student->groups->map(fn ($group): array => [
                    'id' => $group->id,
                    'name' => $group->name,
                    'subject' => $group->subject,
                    'level' => $group->level,
                    'show_url' => route('admin.groups.show', $group),
                    'sessions' => $group->sessions->map(fn ($session): array => [
                        'id' => $session->id,
                        'title' => $session->title,
                        'starts_at' => $session->starts_at?->toDayDateTimeString(),
                        'attendance' => $session->attendanceRecords->where('student_id', $student->id)->first()?->status,
                    ]),
                ]),
                'attendance' => $student->attendanceRecords->map(fn ($attendance): array => [
                    'id' => $attendance->id,
                    'status' => $attendance->status,
                    'notes' => $attendance->notes,
                    'session' => $attendance->session?->title,
                    'group' => $attendance->session?->group?->name,
                    'starts_at' => $attendance->session?->starts_at?->toDayDateTimeString(),
                ]),
                'exam_results' => $student->examResults->map(fn ($result): array => [
                    'id' => $result->id,
                    'title' => $result->exam?->title,
                    'score' => $result->score,
                    'max_score' => $result->exam?->max_score,
                    'percentage' => $result->percentage(),
                    'group' => $result->exam?->group?->name,
                    'schedule' => $result->exam?->start_at && $result->exam?->end_at
                        ? $result->exam->start_at->format('M j, Y g:i A').' - '.$result->exam->end_at->format('g:i A')
                        : '-',
                ]),
                'notifications' => $student->user?->notifications
                    ->where('type', PortalNotificationData::type())
                    ->map(function (DatabaseNotification $notification): array {
                        $data = PortalNotificationData::from($notification);

                        return [
                            'id' => $notification->id,
                            'recipient_role' => $data['audience'],
                            'type' => $data['type'],
                            'title' => $data['title'],
                            'body' => $data['body'],
                            'created_at' => $notification->created_at?->toDayDateTimeString(),
                        ];
                    })
                    ->values()
                    ->all() ?? [],
            ],
        ]);
    }

    public function edit(Student $student): Response
    {
        return Inertia::render('Admin/Students/Edit', [
            'student' => [
                'id' => $student->id,
                'parent_id' => $student->parent_id,
                'name' => $student->name,
                'phone' => $student->phone,
                'date_of_birth' => $student->date_of_birth?->format('Y-m-d'),
                'notes' => $student->notes,
                'is_active' => $student->is_active,
                'student_login' => [
                    'code' => $student->code,
                    'ready' => (bool) $student->user_id,
                ],
            ],
            'parents' => $this->parentOptions(),
            'action' => route('admin.students.update', $student),
            'showUrl' => route('admin.students.show', $student),
        ]);
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $data = $request->validate([
            'parent_id' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $parent = User::query()->whereKey($data['parent_id'])->where('role', 'parent')->firstOrFail();
        $data['parent_id'] = $parent->id;
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $password = $data['password'] ?? null;
        unset($data['password']);

        $student->update($data);
        $this->syncStudentPassword($student->refresh(), $password);

        return redirect()->route('admin.students.show', $student)->with('status', 'Student updated.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return redirect()->route('admin.students.index')->with('status', 'Student deleted.');
    }

    public function toggleStatus(Request $request, Student $student): RedirectResponse
    {
        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $student->update([
            'is_active' => (bool) $data['is_active'],
        ]);

        return back()->with('status', 'Student status updated.');
    }

    private function filters(Request $request): array
    {
        return [
            'search' => trim((string) $request->query('search', '')),
            'status' => (string) $request->query('status', ''),
            'parent_id' => (string) $request->query('parent_id', ''),
            'group_id' => (string) $request->query('group_id', ''),
        ];
    }

    private function filteredIndexQuery(array $filters): Builder
    {
        return Student::query()
            ->with(['parent', 'groups'])
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $query->where(function ($query) use ($filters): void {
                    $query
                        ->where('name', 'like', "%{$filters['search']}%")
                        ->orWhere('code', 'like', "%{$filters['search']}%")
                        ->orWhere('phone', 'like', "%{$filters['search']}%")
                        ->orWhereHas('parent', function ($query) use ($filters): void {
                            $query
                                ->where('name', 'like', "%{$filters['search']}%")
                                ->orWhere('email', 'like', "%{$filters['search']}%");
                        });
                });
            })
            ->when(in_array($filters['status'], ['active', 'inactive'], true), function ($query) use ($filters): void {
                $query->where('is_active', $filters['status'] === 'active');
            })
            ->when($filters['parent_id'] !== '', function ($query) use ($filters): void {
                $query->where('parent_id', $filters['parent_id']);
            })
            ->when($filters['group_id'] !== '', function ($query) use ($filters): void {
                $query->whereHas('groups', fn ($query) => $query->whereKey($filters['group_id']));
            });
    }

    private function parentOptions()
    {
        return User::query()
            ->where('role', 'parent')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    private function syncStudentPassword(Student $student, ?string $password): void
    {
        if (! $student->user || blank($password)) {
            return;
        }

        $student->user->forceFill([
            'password' => Hash::make($password),
        ])->save();
    }
}
