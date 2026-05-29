<?php

namespace Modules\Exams\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Exams\Models\Exam;
use Modules\Exams\Models\ExamAttempt;
use Modules\Exams\Models\ExamAttemptAnswer;
use Modules\Exams\Services\StudentExamService;
use Modules\Exams\Services\SubmitExamAttempt;

class StudentExamAttemptController extends Controller
{
    public function __construct(
        private readonly StudentExamService $examService,
        private readonly SubmitExamAttempt $submitExamAttempt,
    ) {}

    public function show(Request $request, Exam $exam, ExamAttempt $attempt): Response|RedirectResponse
    {
        $student = $request->user()->studentProfile()->firstOrFail();
        $this->authorizeAttempt($exam, $attempt, $student->id);
        $this->autoSubmitIfExpired($attempt);
        $attempt->refresh()->loadMissing('exam.results');

        return Inertia::render('Student/Exams/Attempt', [
            'exam' => [
                'id' => $exam->id,
                'title' => $exam->title,
                'group' => $exam->group ? [
                    'name' => $exam->group->name,
                    'subject' => $exam->group->subject,
                ] : null,
                'max_score' => $exam->max_score,
                'max_allowed_time' => $exam->max_allowed_time,
                'review_mode' => $exam->student_review_mode,
                'answer_url' => route('student.exams.attempt.answer', [$exam, $attempt]),
                'submit_url' => route('student.exams.attempt.submit', [$exam, $attempt]),
                'show_url' => route('student.exams.show', $exam),
                'index_url' => route('student.exams.index'),
            ],
            'attempt' => $this->examService->attemptReviewPayload($attempt),
            'result' => $attempt->exam->results->firstWhere('student_id', $student->id) ? [
                'score' => $attempt->exam->results->firstWhere('student_id', $student->id)?->score,
                'percentage' => $attempt->exam->results->firstWhere('student_id', $student->id)?->percentage(),
                'notes' => $attempt->exam->results->firstWhere('student_id', $student->id)?->notes,
            ] : null,
        ]);
    }

    public function saveAnswer(Request $request, Exam $exam, ExamAttempt $attempt): RedirectResponse
    {
        $student = $request->user()->studentProfile()->firstOrFail();
        $this->authorizeAttempt($exam, $attempt, $student->id);
        $this->autoSubmitIfExpired($attempt);
        abort_if($attempt->fresh()->isSubmitted(), 422);

        $data = $request->validate([
            'exam_question_id' => ['required', 'exists:exam_questions,id'],
            'selected_option_id' => ['nullable', 'exists:exam_question_options,id'],
        ]);

        abort_unless($exam->questions()->whereKey($data['exam_question_id'])->exists(), 422);

        ExamAttemptAnswer::query()->updateOrCreate(
            [
                'exam_attempt_id' => $attempt->id,
                'exam_question_id' => $data['exam_question_id'],
            ],
            [
                'selected_option_id' => $data['selected_option_id'] ?? null,
            ],
        );

        return back()->with('status', 'Answer saved.');
    }

    public function submit(Request $request, Exam $exam, ExamAttempt $attempt): RedirectResponse
    {
        $student = $request->user()->studentProfile()->firstOrFail();
        $this->authorizeAttempt($exam, $attempt, $student->id);

        $this->autoSubmitIfExpired($attempt);

        if (! $attempt->fresh()->isSubmitted()) {
            $this->submitExamAttempt->handle($attempt, 'submitted');
        }

        return redirect()->route('student.exams.attempt.show', [$exam, $attempt])->with('status', 'Exam submitted.');
    }

    private function authorizeAttempt(Exam $exam, ExamAttempt $attempt, int $studentId): void
    {
        abort_unless($attempt->exam_id === $exam->id && $attempt->student_id === $studentId, 403);
    }

    private function autoSubmitIfExpired(ExamAttempt $attempt): void
    {
        if (! $attempt->isSubmitted() && $attempt->expires_at && $attempt->expires_at->lte(now())) {
            $this->submitExamAttempt->handle($attempt, 'auto_submitted');
        }
    }
}
