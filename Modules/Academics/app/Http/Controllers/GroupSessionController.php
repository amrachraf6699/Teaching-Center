<?php

namespace Modules\Academics\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Academics\Models\GroupSession;
use Modules\Academics\Models\TeachingGroup;

class GroupSessionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Sessions/Index', [
            'sessions' => GroupSession::query()
                ->with('group')
                ->latest('starts_at')
                ->paginate(20)
                ->through(fn (GroupSession $session): array => [
                    'id' => $session->id,
                    'title' => $session->title,
                    'starts_at' => $session->starts_at?->toDayDateTimeString(),
                    'ends_at' => $session->ends_at?->toDayDateTimeString(),
                    'group' => $session->group ? [
                        'id' => $session->group->id,
                        'name' => $session->group->name,
                        'subject' => $session->group->subject,
                    ] : null,
                    'show_url' => route('admin.sessions.show', $session),
                    'edit_url' => route('admin.sessions.edit', $session),
                    'delete_url' => route('admin.sessions.destroy', $session),
                ]),
            'createUrl' => route('admin.sessions.create'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Sessions/Create', [
            'groups' => TeachingGroup::query()->orderBy('name')->get(['id', 'name', 'subject']),
            'action' => route('admin.sessions.store'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'teaching_group_id' => ['required', 'exists:teaching_groups,id'],
            'title' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'notes' => ['nullable', 'string'],
        ]);

        GroupSession::create($data);

        return redirect()->route('admin.sessions.index')->with('status', 'Session created.');
    }

    public function show(GroupSession $session): Response
    {
        $session->load(['group.students.parent', 'attendanceRecords.student.parent']);

        return Inertia::render('Admin/Sessions/Show', [
            'session' => [
                'id' => $session->id,
                'title' => $session->title,
                'starts_at' => $session->starts_at?->toDayDateTimeString(),
                'ends_at' => $session->ends_at?->toDayDateTimeString(),
                'notes' => $session->notes,
                'edit_url' => route('admin.sessions.edit', $session),
                'index_url' => route('admin.sessions.index'),
                'group' => $session->group ? [
                    'id' => $session->group->id,
                    'name' => $session->group->name,
                    'subject' => $session->group->subject,
                    'show_url' => route('admin.groups.show', $session->group),
                ] : null,
                'students' => $session->group?->students->map(fn ($student): array => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'code' => $student->code,
                    'parent' => $student->parent?->name,
                    'attendance' => $session->attendanceRecords->where('student_id', $student->id)->first()?->status,
                    'show_url' => route('admin.students.show', $student),
                ])->values() ?? [],
                'attendance' => $session->attendanceRecords->map(fn ($attendance): array => [
                    'id' => $attendance->id,
                    'student' => $attendance->student?->name,
                    'parent' => $attendance->student?->parent?->name,
                    'status' => $attendance->status,
                    'notes' => $attendance->notes,
                ]),
            ],
        ]);
    }

    public function edit(GroupSession $session): Response
    {
        return Inertia::render('Admin/Sessions/Edit', [
            'session' => [
                'id' => $session->id,
                'teaching_group_id' => $session->teaching_group_id,
                'title' => $session->title,
                'starts_at' => $session->starts_at?->format('Y-m-d\TH:i'),
                'ends_at' => $session->ends_at?->format('Y-m-d\TH:i'),
                'notes' => $session->notes,
            ],
            'groups' => TeachingGroup::query()->orderBy('name')->get(['id', 'name', 'subject']),
            'action' => route('admin.sessions.update', $session),
            'showUrl' => route('admin.sessions.show', $session),
        ]);
    }

    public function update(Request $request, GroupSession $session): RedirectResponse
    {
        $data = $request->validate([
            'teaching_group_id' => ['required', 'exists:teaching_groups,id'],
            'title' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'notes' => ['nullable', 'string'],
        ]);

        $session->update($data);

        return redirect()->route('admin.sessions.show', $session)->with('status', 'Session updated.');
    }

    public function destroy(GroupSession $session): RedirectResponse
    {
        $session->delete();

        return redirect()->route('admin.sessions.index')->with('status', 'Session deleted.');
    }
}
