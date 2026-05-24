<?php

namespace Modules\Exams\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\TableExport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Academics\Models\TeachingGroup;
use Modules\Exams\Models\Exam;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ExamController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $this->filters($request);

        return Inertia::render('Admin/Exams/Index', [
            'exams' => $this->filteredIndexQuery($filters)
                ->latest('exam_date')
                ->paginate(20)
                ->withQueryString()
                ->through(fn (Exam $exam): array => [
                    'id' => $exam->id,
                    'title' => $exam->title,
                    'exam_date' => $exam->exam_date?->toFormattedDateString(),
                    'max_score' => $exam->max_score,
                    'group' => $exam->group ? [
                        'id' => $exam->group->id,
                        'name' => $exam->group->name,
                        'subject' => $exam->group->subject,
                    ] : null,
                    'show_url' => route('admin.exams.show', $exam),
                    'edit_url' => route('admin.exams.edit', $exam),
                    'delete_url' => route('admin.exams.destroy', $exam),
                ]),
            'filters' => $filters,
            'groupOptions' => TeachingGroup::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (TeachingGroup $group): array => [
                    'value' => (string) $group->id,
                    'label' => $group->name,
                ]),
            'indexUrl' => route('admin.exams.index'),
            'createUrl' => route('admin.exams.create'),
            'exportUrls' => [
                'csv' => route('admin.exams.export', 'csv'),
                'pdf' => route('admin.exams.export', 'pdf'),
            ],
        ]);
    }

    public function export(Request $request, string $format): SymfonyResponse
    {
        abort_unless(in_array($format, ['csv', 'pdf'], true), 404);

        $rows = $this->filteredIndexQuery($this->filters($request))
            ->latest('exam_date')
            ->get()
            ->map(fn (Exam $exam): array => [
                $exam->title,
                $exam->group?->name ?? '-',
                $exam->exam_date?->toFormattedDateString() ?? '-',
                (string) $exam->max_score,
            ])
            ->all();

        $headers = ['Exam', 'Group', 'Date', 'Max Score'];
        $filename = 'exams-export-'.now()->format('Ymd_His').'.'.$format;

        if ($format === 'csv') {
            return TableExport::csv($filename, $headers, $rows);
        }

        return TableExport::pdf($filename, 'Exams Export', $headers, $rows);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Exams/Create', [
            'groups' => TeachingGroup::query()->orderBy('name')->get(['id', 'name', 'subject']),
            'action' => route('admin.exams.store'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'teaching_group_id' => ['required', 'exists:teaching_groups,id'],
            'title' => ['required', 'string', 'max:255'],
            'exam_date' => ['required', 'date'],
            'max_score' => ['required', 'numeric', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        Exam::create($data);

        return redirect()->route('admin.exams.index')->with('status', 'Exam created.');
    }

    public function show(Exam $exam): Response
    {
        $exam->load(['group.students.parent', 'results.student.parent']);

        return Inertia::render('Admin/Exams/Show', [
            'exam' => [
                'id' => $exam->id,
                'title' => $exam->title,
                'exam_date' => $exam->exam_date?->toFormattedDateString(),
                'max_score' => $exam->max_score,
                'notes' => $exam->notes,
                'edit_url' => route('admin.exams.edit', $exam),
                'index_url' => route('admin.exams.index'),
                'result_action' => route('admin.exam-results.store', $exam),
                'group' => $exam->group ? [
                    'id' => $exam->group->id,
                    'name' => $exam->group->name,
                    'subject' => $exam->group->subject,
                    'show_url' => route('admin.groups.show', $exam->group),
                ] : null,
                'students' => $exam->group?->students->map(function ($student) use ($exam): array {
                    $result = $exam->results->where('student_id', $student->id)->first();

                    return [
                        'id' => $student->id,
                        'name' => $student->name,
                        'code' => $student->code,
                        'parent' => $student->parent?->name,
                        'show_url' => route('admin.students.show', $student),
                        'result' => $result ? [
                            'id' => $result->id,
                            'score' => $result->score,
                            'percentage' => $result->percentage(),
                            'notes' => $result->notes,
                        ] : null,
                    ];
                })->values() ?? [],
                'results' => $exam->results->map(fn ($result): array => [
                    'id' => $result->id,
                    'student' => $result->student?->name,
                    'parent' => $result->student?->parent?->name,
                    'score' => $result->score,
                    'percentage' => $result->percentage(),
                    'notes' => $result->notes,
                ]),
            ],
        ]);
    }

    public function edit(Exam $exam): Response
    {
        return Inertia::render('Admin/Exams/Edit', [
            'exam' => [
                'id' => $exam->id,
                'teaching_group_id' => $exam->teaching_group_id,
                'title' => $exam->title,
                'exam_date' => $exam->exam_date?->format('Y-m-d'),
                'max_score' => $exam->max_score,
                'notes' => $exam->notes,
            ],
            'groups' => TeachingGroup::query()->orderBy('name')->get(['id', 'name', 'subject']),
            'action' => route('admin.exams.update', $exam),
            'showUrl' => route('admin.exams.show', $exam),
        ]);
    }

    public function update(Request $request, Exam $exam): RedirectResponse
    {
        $data = $request->validate([
            'teaching_group_id' => ['required', 'exists:teaching_groups,id'],
            'title' => ['required', 'string', 'max:255'],
            'exam_date' => ['required', 'date'],
            'max_score' => ['required', 'numeric', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        $exam->update($data);

        return redirect()->route('admin.exams.show', $exam)->with('status', 'Exam updated.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $exam->delete();

        return redirect()->route('admin.exams.index')->with('status', 'Exam deleted.');
    }

    private function filters(Request $request): array
    {
        return [
            'search' => trim((string) $request->query('search', '')),
            'group' => (string) $request->query('group', ''),
        ];
    }

    private function filteredIndexQuery(array $filters): Builder
    {
        return Exam::query()
            ->with('group')
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $query->where(function ($query) use ($filters): void {
                    $query
                        ->where('title', 'like', "%{$filters['search']}%")
                        ->orWhereHas('group', function ($query) use ($filters): void {
                            $query
                                ->where('name', 'like', "%{$filters['search']}%")
                                ->orWhere('subject', 'like', "%{$filters['search']}%");
                        });
                });
            })
            ->when($filters['group'] !== '', function ($query) use ($filters): void {
                $query->where('teaching_group_id', $filters['group']);
            });
    }
}
