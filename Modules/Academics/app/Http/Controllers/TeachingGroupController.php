<?php

namespace Modules\Academics\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
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
        $filters = $this->filters($request);

        return Inertia::render('Admin/Groups/Index', [
            'groups' => $this->filteredIndexQuery($filters)
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
            'filters' => $filters,
            'indexUrl' => route('admin.groups.index'),
            'createUrl' => route('admin.groups.create'),
            'exportUrls' => [
                'csv' => route('admin.groups.export', 'csv'),
                'pdf' => route('admin.groups.export', 'pdf'),
            ],
            'importOptions' => [
                $this->importOption('groups', 'Groups'),
                $this->importOption('enrollments', 'Enrollments'),
            ],
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

        return redirect()->route('admin.groups.index')->with('status', __('flash.group.created'));
    }

    public function show(TeachingGroup $group): Response
    {
        $group->load([
            'students.parent',
            'sessions.attendanceRecords.student',
            'exams.results.student',
            'timetable.entries',
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
                'timetable' => $group->timetable ? [
                    'show_url' => route('admin.timetables.show', $group->timetable),
                    'edit_url' => route('admin.timetables.edit', $group->timetable),
                    'entries' => $group->timetable->entries->map(fn ($entry): array => [
                        'day' => ucfirst($entry->day_of_week),
                        'time_range' => substr((string) $entry->starts_at, 0, 5).' - '.substr((string) $entry->ends_at, 0, 5),
                    ])->values()->all(),
                ] : null,
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
                    'source_label' => $session->isGenerated() ? 'Generated' : 'Manual',
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
                    'schedule' => $exam->start_at && $exam->end_at
                        ? $exam->start_at->format('M j, Y g:i A').' - '.$exam->end_at->format('g:i A')
                        : '-',
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

        return redirect()->route('admin.groups.show', $group)->with('status', __('flash.group.updated'));
    }

    public function destroy(TeachingGroup $group): RedirectResponse
    {
        $group->delete();

        return redirect()->route('admin.groups.index')->with('status', __('flash.group.deleted'));
    }

    private function filters(Request $request): array
    {
        return [
            'search' => trim((string) $request->query('search', '')),
            'status' => (string) $request->query('status', ''),
        ];
    }

    private function filteredIndexQuery(array $filters): Builder
    {
        return TeachingGroup::query()
            ->withCount('students')
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $query->where(function ($query) use ($filters): void {
                    $query
                        ->where('name', 'like', "%{$filters['search']}%")
                        ->orWhere('subject', 'like', "%{$filters['search']}%")
                        ->orWhere('level', 'like', "%{$filters['search']}%");
                });
            })
            ->when(in_array($filters['status'], ['active', 'inactive'], true), function ($query) use ($filters): void {
                $query->where('is_active', $filters['status'] === 'active');
            });
    }

    private function importOption(string $type, string $label): array
    {
        return [
            'value' => $type,
            'label' => $label,
            'store_url' => route('admin.imports.store', $type),
            'template_csv_url' => route('admin.imports.template', ['type' => $type, 'format' => 'csv']),
            'template_xlsx_url' => route('admin.imports.template', ['type' => $type, 'format' => 'xlsx']),
        ];
    }
}
