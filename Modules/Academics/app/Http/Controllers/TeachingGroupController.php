<?php

namespace Modules\Academics\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Academics\Models\TeachingGroup;
use Modules\People\Models\Student;

class TeachingGroupController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');

        return Inertia::render('Admin/Groups/Index', [
            'groups' => TeachingGroup::query()
                ->withCount('students')
                ->when($search !== '', function ($query) use ($search): void {
                    $query->where(function ($query) use ($search): void {
                        $query
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('subject', 'like', "%{$search}%")
                            ->orWhere('level', 'like', "%{$search}%");
                    });
                })
                ->when(in_array($status, ['active', 'inactive'], true), function ($query) use ($status): void {
                    $query->where('is_active', $status === 'active');
                })
                ->latest()
                ->paginate(20)
                ->withQueryString()
                ->through(fn (TeachingGroup $group): array => [
                    'id' => $group->id,
                    'name' => $group->name,
                    'subject' => $group->subject,
                    'level' => $group->level,
                    'students_count' => $group->students_count,
                    'is_active' => $group->is_active,
                    'show_url' => route('admin.groups.show', $group),
                    'edit_url' => route('admin.groups.edit', $group),
                    'delete_url' => route('admin.groups.destroy', $group),
                    'created_at' => $group->created_at?->toFormattedDateString(),
                ]),
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
            'indexUrl' => route('admin.groups.index'),
            'createUrl' => route('admin.groups.create'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Groups/Create', [
            'students' => Student::query()->orderBy('name')->get(['id', 'name', 'code']),
            'action' => route('admin.groups.store'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'level' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['exists:students,id'],
        ]);

        $studentIds = $data['student_ids'] ?? [];
        unset($data['student_ids']);

        $group = TeachingGroup::create($data);
        $group->students()->sync($studentIds);

        return redirect()->route('admin.groups.index')->with('status', 'Group created.');
    }

    public function show(TeachingGroup $group): Response
    {
        $group->load([
            'students.parent',
            'sessions.attendanceRecords.student',
            'exams.results.student',
        ])->loadCount(['students', 'sessions', 'exams']);

        return Inertia::render('Admin/Groups/Show', [
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'subject' => $group->subject,
                'level' => $group->level,
                'description' => $group->description,
                'is_active' => $group->is_active,
                'students_count' => $group->students_count,
                'sessions_count' => $group->sessions_count,
                'exams_count' => $group->exams_count,
                'created_at' => $group->created_at?->toDayDateTimeString(),
                'edit_url' => route('admin.groups.edit', $group),
                'index_url' => route('admin.groups.index'),
                'students' => $group->students->map(fn ($student): array => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'code' => $student->code,
                    'parent' => $student->parent?->name,
                    'show_url' => route('admin.students.show', $student),
                ]),
                'sessions' => $group->sessions->map(fn ($session): array => [
                    'id' => $session->id,
                    'title' => $session->title,
                    'starts_at' => $session->starts_at?->toDayDateTimeString(),
                    'ends_at' => $session->ends_at?->toDayDateTimeString(),
                    'show_url' => route('admin.sessions.show', $session),
                    'attendance' => $session->attendanceRecords->map(fn ($attendance): array => [
                        'id' => $attendance->id,
                        'student' => $attendance->student?->name,
                        'status' => $attendance->status,
                        'notes' => $attendance->notes,
                    ]),
                ]),
                'exams' => $group->exams->map(fn ($exam): array => [
                    'id' => $exam->id,
                    'title' => $exam->title,
                    'exam_date' => $exam->exam_date?->toFormattedDateString(),
                    'max_score' => $exam->max_score,
                    'show_url' => route('admin.exams.show', $exam),
                    'results' => $exam->results->map(fn ($result): array => [
                        'id' => $result->id,
                        'student' => $result->student?->name,
                        'score' => $result->score,
                        'percentage' => $result->percentage(),
                    ]),
                ]),
            ],
        ]);
    }

    public function edit(TeachingGroup $group): Response
    {
        return Inertia::render('Admin/Groups/Edit', [
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'subject' => $group->subject,
                'level' => $group->level,
                'description' => $group->description,
                'is_active' => $group->is_active,
                'student_ids' => $group->students()->pluck('students.id')->all(),
            ],
            'students' => Student::query()->orderBy('name')->get(['id', 'name', 'code']),
            'action' => route('admin.groups.update', $group),
            'showUrl' => route('admin.groups.show', $group),
        ]);
    }

    public function update(Request $request, TeachingGroup $group): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'level' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['exists:students,id'],
        ]);

        $studentIds = $data['student_ids'] ?? [];
        unset($data['student_ids']);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $group->update($data);
        $group->students()->sync($studentIds);

        return redirect()->route('admin.groups.show', $group)->with('status', 'Group updated.');
    }

    public function destroy(TeachingGroup $group): RedirectResponse
    {
        $group->delete();

        return redirect()->route('admin.groups.index')->with('status', 'Group deleted.');
    }
}
