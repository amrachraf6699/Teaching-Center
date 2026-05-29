<?php

namespace Modules\Exams\Services;

use Illuminate\Support\Collection;
use Modules\Exams\Models\Exam;
use Modules\Exams\Models\ExamAttempt;
use Modules\Exams\Models\ExamQuestion;
use Modules\Exams\Models\ExamResult;
use Modules\People\Models\Student;

class StudentExamService
{
    public function indexDataForStudent(Student $student): array
    {
        $exams = Exam::query()
            ->with([
                'group',
                'results' => fn ($query) => $query->where('student_id', $student->id),
                'attempts' => fn ($query) => $query->where('student_id', $student->id),
            ])
            ->whereIn('teaching_group_id', $student->groups->pluck('id')->all())
            ->orderBy('start_at')
            ->get();

        $upcoming = [];
        $conducted = [];

        foreach ($exams as $exam) {
            $payload = $this->summarizeExamForStudent($exam, $student);

            if ($payload['bucket'] === 'upcoming') {
                $upcoming[] = $payload;
            } else {
                $conducted[] = $payload;
            }
        }

        return [
            'upcoming' => $upcoming,
            'conducted' => $conducted,
        ];
    }

    public function summarizeExamForStudent(Exam $exam, Student $student): array
    {
        $attempt = $exam->attempts->first();
        $result = $exam->results->first();
        $now = now();
        $isWindowOpen = $exam->start_at && $exam->end_at && $now->between($exam->start_at, $exam->end_at);
        $isEnded = $exam->end_at?->lt($now) ?? false;
        $bucket = ($attempt || $result || $isEnded) ? 'conducted' : 'upcoming';
        $availability = 'scheduled';

        if ($attempt?->status === 'in_progress') {
            $availability = 'in_progress';
        } elseif ($attempt?->isSubmitted() || $result) {
            $availability = 'submitted';
        } elseif ($isWindowOpen) {
            $availability = 'available';
        } elseif ($isEnded) {
            $availability = 'closed';
        }

        return [
            'id' => $exam->id,
            'title' => $exam->title,
            'group' => $exam->group ? [
                'id' => $exam->group->id,
                'name' => $exam->group->name,
                'subject' => $exam->group->subject,
            ] : null,
            'schedule' => $exam->start_at && $exam->end_at
                ? $exam->start_at->format('M j, Y g:i A').' - '.$exam->end_at->format('g:i A')
                : '-',
            'start_at' => $exam->start_at?->toIso8601String(),
            'end_at' => $exam->end_at?->toIso8601String(),
            'max_allowed_time' => $exam->max_allowed_time,
            'max_score' => $exam->max_score,
            'notes' => $exam->notes,
            'review_mode' => $exam->student_review_mode,
            'bucket' => $bucket,
            'availability' => $availability,
            'has_attempt' => (bool) $attempt,
            'attempt_status' => $attempt?->status,
            'remaining_seconds' => $attempt && ! $attempt->isSubmitted()
                ? max(0, ($attempt->expires_at?->timestamp ?? 0) - $now->timestamp)
                : null,
            'score' => $result?->score,
            'percentage' => $result?->percentage(),
            'result_notes' => $result?->notes,
            'show_url' => route('student.exams.show', $exam),
            'attempt_url' => $attempt ? route('student.exams.attempt.show', [$exam, $attempt]) : null,
        ];
    }

    public function calculateAttemptExpiry(Exam $exam)
    {
        $timeLimitEnd = now()->copy()->addMinutes((int) $exam->max_allowed_time);

        if (! $exam->end_at) {
            return $timeLimitEnd;
        }

        return $timeLimitEnd->lt($exam->end_at) ? $timeLimitEnd : $exam->end_at->copy();
    }

    public function gradeAttempt(ExamAttempt $attempt): ExamResult
    {
        $attempt->loadMissing([
            'exam.questions.options',
            'student',
            'answers',
        ]);

        $total = 0.0;

        foreach ($attempt->exam->questions as $question) {
            $answer = $attempt->answers->firstWhere('exam_question_id', $question->id);
            $correctOptionId = $question->options->firstWhere('is_correct', true)?->id;
            $isCorrect = $answer && $answer->selected_option_id === $correctOptionId;
            $earnedPoints = $isCorrect ? (float) $question->points : 0.0;

            if ($answer) {
                $answer->forceFill([
                    'is_correct' => $isCorrect,
                    'earned_points' => $earnedPoints,
                ])->save();
            }

            $total += $earnedPoints;
        }

        return ExamResult::query()->updateOrCreate(
            [
                'exam_id' => $attempt->exam_id,
                'student_id' => $attempt->student_id,
            ],
            [
                'score' => $total,
                'notes' => $attempt->status === 'auto_submitted'
                    ? 'Auto-submitted when time expired.'
                    : 'Submitted from student portal.',
            ],
        );
    }

    public function attemptReviewPayload(ExamAttempt $attempt): array
    {
        $attempt->loadMissing([
            'exam.group',
            'exam.questions.options',
            'answers.selectedOption',
        ]);
        $canReviewQuestions = $attempt->isSubmitted() && $attempt->exam->canStudentReviewQuestions();

        return [
            'id' => $attempt->id,
            'status' => $attempt->status,
            'started_at' => $attempt->started_at?->toDayDateTimeString(),
            'submitted_at' => $attempt->submitted_at?->toDayDateTimeString(),
            'expires_at' => $attempt->expires_at?->toIso8601String(),
            'remaining_seconds' => $attempt->isSubmitted()
                ? 0
                : max(0, ($attempt->expires_at?->timestamp ?? 0) - now()->timestamp),
            'questions' => $attempt->exam->questions->map(function (ExamQuestion $question) use ($attempt, $canReviewQuestions): array {
                $answer = $attempt->answers->firstWhere('exam_question_id', $question->id);

                return [
                    'id' => $question->id,
                    'type' => $question->type,
                    'prompt' => $question->prompt,
                    'points' => $question->points,
                    'selected_option_id' => $answer?->selected_option_id,
                    'is_correct' => $canReviewQuestions ? $answer?->is_correct : null,
                    'earned_points' => $canReviewQuestions ? $answer?->earned_points : null,
                    'options' => $question->options->map(fn ($option): array => [
                        'id' => $option->id,
                        'label' => $option->label,
                        'is_correct' => $canReviewQuestions ? $option->is_correct : null,
                    ])->values()->all(),
                ];
            })->values()->all(),
        ];
    }
}
