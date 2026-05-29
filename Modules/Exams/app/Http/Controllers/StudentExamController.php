<?php

namespace Modules\Exams\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Exams\Models\Exam;
use Modules\Exams\Models\ExamAttempt;
use Modules\Exams\Services\StudentExamService;

class StudentExamController extends Controller
{
    public function __construct(private readonly StudentExamService $examService) {}

    public function index(Request $request): Response
    {
        $student = $request->user()->studentProfile()->with('groups')->firstOrFail();
        $data = $this->examService->indexDataForStudent($student);

        return Inertia::render('Student/Exams/Index', [
            'upcomingExams' => $data['upcoming'],
            'conductedExams' => $data['conducted'],
        ]);
    }

    public function show(Request $request, Exam $exam): Response
    {
        $student = $request->user()->studentProfile()->with('groups')->firstOrFail();
        abort_unless($student->groups->contains('id', $exam->teaching_group_id), 403);

        $exam->load([
            'group',
            'questions.options',
            'results' => fn ($query) => $query->where('student_id', $student->id),
            'attempts' => fn ($query) => $query->where('student_id', $student->id)->with('answers'),
        ]);

        $summary = $this->examService->summarizeExamForStudent($exam, $student);
        $attempt = $exam->attempts->first();

        return Inertia::render('Student/Exams/Show', [
            'exam' => [
                ...$summary,
                'questions_count' => $exam->questions->count(),
                'index_url' => route('student.exams.index'),
                'start_url' => (! $attempt && $summary['availability'] === 'available')
                    ? route('student.exams.start', $exam)
                    : null,
                'attempt_url' => $attempt ? route('student.exams.attempt.show', [$exam, $attempt]) : null,
            ],
        ]);
    }

    public function start(Request $request, Exam $exam): RedirectResponse
    {
        $student = $request->user()->studentProfile()->with('groups')->firstOrFail();
        abort_unless($student->groups->contains('id', $exam->teaching_group_id), 403);
        abort_unless($exam->start_at && $exam->end_at && now()->between($exam->start_at, $exam->end_at), 422);

        $attempt = DB::transaction(function () use ($exam, $student): ExamAttempt {
            $existing = ExamAttempt::query()
                ->where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->first();

            abort_if($existing, 422, 'Attempt already exists.');

            return ExamAttempt::query()->create([
                'exam_id' => $exam->id,
                'student_id' => $student->id,
                'started_at' => now(),
                'expires_at' => $this->examService->calculateAttemptExpiry($exam),
                'status' => 'in_progress',
            ]);
        });

        return redirect()->route('student.exams.attempt.show', [$exam, $attempt]);
    }
}
