<?php

namespace Modules\Exams\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Academics\Models\TeachingGroup;
use Modules\Exams\Actions\UpsertExam;
use Modules\Exams\Models\Exam;
use Modules\Exams\Models\ExamAttempt;
use Modules\Exams\Models\ExamQuestion;

class ExamController extends Controller
{
    public function __construct(private readonly UpsertExam $upsertExam) {}

    public function index(Request $request): Response
    {
        $filters = $this->filters($request);

        return Inertia::render('Admin/Exams/Index', [
            'exams' => $this->filteredIndexQuery($filters)
                ->latest('start_at')
                ->paginate(20)
                ->withQueryString()
                ->through(fn (Exam $exam): array => [
                    'id' => $exam->id,
                    'title' => $exam->title,
                    'schedule' => $this->scheduleSummary($exam),
                    'max_allowed_time' => $this->allowedTimeLabel($exam->max_allowed_time),
                    'max_score' => $exam->max_score,
                    'question_count' => $exam->questions_count,
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

    public function create(): Response
    {
        return Inertia::render('Admin/Exams/Create', [
            'groups' => TeachingGroup::query()->orderBy('name')->get(['id', 'name', 'subject']),
            'action' => route('admin.exams.store'),
            'questionTypes' => $this->questionTypes(),
            'reviewModes' => $this->reviewModes(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        [$attributes, $questions] = $this->validatedPayload($request);

        $this->upsertExam->handle($attributes, $questions);

        return redirect()->route('admin.exams.index')->with('status', 'Exam created.');
    }

    public function show(Exam $exam): Response
    {
        $exam->load(['group.students.parent', 'results.student.parent', 'questions.options', 'attempts.student']);

        return Inertia::render('Admin/Exams/Show', [
            'exam' => [
                'id' => $exam->id,
                'title' => $exam->title,
                'schedule' => $this->scheduleSummary($exam),
                'start_at' => $exam->start_at?->toDayDateTimeString(),
                'end_at' => $exam->end_at?->toDayDateTimeString(),
                'max_allowed_time' => $this->allowedTimeLabel($exam->max_allowed_time),
                'max_score' => $exam->max_score,
                'notes' => $exam->notes,
                'student_review_mode' => $exam->student_review_mode,
                'student_review_mode_label' => $exam->canStudentReviewQuestions() ? 'Question review' : 'Score only',
                'edit_url' => route('admin.exams.edit', $exam),
                'index_url' => route('admin.exams.index'),
                'result_action' => route('admin.exam-results.store', $exam),
                'group' => $exam->group ? [
                    'id' => $exam->group->id,
                    'name' => $exam->group->name,
                    'subject' => $exam->group->subject,
                    'show_url' => route('admin.groups.show', $exam->group),
                ] : null,
                'questions' => $exam->questions->map(fn (ExamQuestion $question): array => [
                    'id' => $question->id,
                    'type' => $question->type,
                    'type_label' => $question->type === 'true_false' ? 'True / False' : 'MCQ',
                    'prompt' => $question->prompt,
                    'points' => $question->points,
                    'correct_answer' => $question->options->firstWhere('is_correct', true)?->label,
                    'options' => $question->options->map(fn ($option): array => [
                        'id' => $option->id,
                        'label' => $option->label,
                        'is_correct' => $option->is_correct,
                    ])->values()->all(),
                ])->values()->all(),
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
                        'attempt' => ($attempt = $exam->attempts->where('student_id', $student->id)->first()) ? [
                            'id' => $attempt->id,
                            'status' => $attempt->status,
                            'started_at' => $attempt->started_at?->toDayDateTimeString(),
                            'submitted_at' => $attempt->submitted_at?->toDayDateTimeString(),
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
                'attempts' => $exam->attempts->map(fn (ExamAttempt $attempt): array => [
                    'id' => $attempt->id,
                    'student' => $attempt->student?->name,
                    'status' => $attempt->status,
                    'started_at' => $attempt->started_at?->toDayDateTimeString(),
                    'submitted_at' => $attempt->submitted_at?->toDayDateTimeString(),
                ])->values()->all(),
            ],
        ]);
    }

    public function edit(Exam $exam): Response
    {
        $exam->load('questions.options');

        return Inertia::render('Admin/Exams/Edit', [
            'exam' => [
                'id' => $exam->id,
                'teaching_group_id' => $exam->teaching_group_id,
                'title' => $exam->title,
                'start_at' => $exam->start_at?->format('Y-m-d\TH:i'),
                'end_at' => $exam->end_at?->format('Y-m-d\TH:i'),
                'max_allowed_time' => $exam->max_allowed_time,
                'max_score' => $exam->max_score,
                'notes' => $exam->notes,
                'student_review_mode' => $exam->student_review_mode,
                'questions' => $exam->questions->map(fn (ExamQuestion $question): array => [
                    'id' => $question->id,
                    'type' => $question->type,
                    'prompt' => $question->prompt,
                    'points' => $question->points,
                    'correct_boolean' => $question->type === 'true_false'
                        ? (string) ($question->options->firstWhere('is_correct', true)?->label === 'True' ? 'true' : 'false')
                        : '',
                    'options' => $question->type === 'mcq'
                        ? $question->options->map(fn ($option): array => [
                            'id' => $option->id,
                            'label' => $option->label,
                            'is_correct' => $option->is_correct,
                        ])->values()->all()
                        : $question->options->map(fn ($option): array => [
                            'id' => $option->id,
                            'label' => $option->label,
                            'is_correct' => $option->is_correct,
                        ])->values()->all(),
                ])->values()->all(),
            ],
            'groups' => TeachingGroup::query()->orderBy('name')->get(['id', 'name', 'subject']),
            'action' => route('admin.exams.update', $exam),
            'showUrl' => route('admin.exams.show', $exam),
            'questionTypes' => $this->questionTypes(),
            'reviewModes' => $this->reviewModes(),
        ]);
    }

    public function update(Request $request, Exam $exam): RedirectResponse
    {
        [$attributes, $questions] = $this->validatedPayload($request);

        $this->upsertExam->handle($attributes, $questions, $exam);

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
            ->withCount('questions')
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

    private function validatedPayload(Request $request): array
    {
        $data = $request->validate([
            'teaching_group_id' => ['required', 'exists:teaching_groups,id'],
            'title' => ['required', 'string', 'max:255'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'max_allowed_time' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
            'student_review_mode' => ['nullable', 'in:score_only,question_review'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.id' => ['nullable', 'integer'],
            'questions.*.type' => ['required', 'in:true_false,mcq'],
            'questions.*.prompt' => ['required', 'string'],
            'questions.*.points' => ['required', 'numeric', 'min:0.01'],
            'questions.*.correct_boolean' => ['nullable', 'in:true,false'],
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.options.*.id' => ['nullable', 'integer'],
            'questions.*.options.*.label' => ['nullable', 'string'],
            'questions.*.options.*.is_correct' => ['nullable', 'boolean'],
        ]);

        $questions = collect($data['questions'])
            ->values()
            ->map(function (array $question, int $index): array {
                if ($question['type'] === 'true_false') {
                    if (! in_array($question['correct_boolean'] ?? null, ['true', 'false'], true)) {
                        throw ValidationException::withMessages([
                            "questions.$index.correct_boolean" => 'Select the correct answer for this true or false question.',
                        ]);
                    }

                    return [
                        'id' => $question['id'] ?? null,
                        'type' => 'true_false',
                        'prompt' => $question['prompt'],
                        'points' => $question['points'],
                        'options' => [
                            ['id' => $question['options'][0]['id'] ?? null, 'label' => 'True', 'is_correct' => $question['correct_boolean'] === 'true'],
                            ['id' => $question['options'][1]['id'] ?? null, 'label' => 'False', 'is_correct' => $question['correct_boolean'] === 'false'],
                        ],
                    ];
                }

                $options = collect($question['options'] ?? [])
                    ->map(fn (array $option): array => [
                        'label' => trim((string) ($option['label'] ?? '')),
                        'is_correct' => (bool) ($option['is_correct'] ?? false),
                    ])
                    ->filter(fn (array $option): bool => $option['label'] !== '')
                    ->values();

                if ($options->count() < 2 || $options->count() > 6) {
                    throw ValidationException::withMessages([
                        "questions.$index.options" => 'Each MCQ question must have between 2 and 6 options.',
                    ]);
                }

                if ($options->where('is_correct', true)->count() !== 1) {
                    throw ValidationException::withMessages([
                        "questions.$index.options" => 'Each MCQ question must have exactly one correct option.',
                    ]);
                }

                return [
                    'id' => $question['id'] ?? null,
                    'type' => 'mcq',
                    'prompt' => $question['prompt'],
                    'points' => $question['points'],
                    'options' => $options->all(),
                ];
            })->all();

        return [[
            'teaching_group_id' => $data['teaching_group_id'],
            'title' => $data['title'],
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
            'max_allowed_time' => $data['max_allowed_time'],
            'notes' => $data['notes'] ?? null,
            'student_review_mode' => $data['student_review_mode'] ?? 'score_only',
        ], $questions];
    }

    private function scheduleSummary(Exam $exam): string
    {
        if (! $exam->start_at || ! $exam->end_at) {
            return '-';
        }

        return $exam->start_at->format('M j, Y g:i A').' - '.$exam->end_at->format('g:i A');
    }

    private function allowedTimeLabel(?int $minutes): string
    {
        if (! $minutes) {
            return '-';
        }

        return $minutes.' min';
    }

    private function questionTypes(): array
    {
        return [
            ['value' => 'true_false', 'label' => 'True / False'],
            ['value' => 'mcq', 'label' => 'MCQ'],
        ];
    }

    private function reviewModes(): array
    {
        return [
            ['value' => 'score_only', 'label' => 'Score Only'],
            ['value' => 'question_review', 'label' => 'Question Review'],
        ];
    }
}
