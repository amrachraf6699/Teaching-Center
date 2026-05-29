<?php

namespace Modules\Exams\Services;

use Illuminate\Support\Facades\DB;
use Modules\Exams\Models\ExamAttempt;
use Modules\Exams\Models\ExamResult;

class SubmitExamAttempt
{
    public function __construct(private readonly StudentExamService $examService) {}

    public function handle(ExamAttempt $attempt, string $status = 'submitted'): ExamResult
    {
        return DB::transaction(function () use ($attempt, $status): ExamResult {
            $attempt->refresh();

            if ($attempt->isSubmitted()) {
                return $attempt->exam->results()->where('student_id', $attempt->student_id)->firstOrFail();
            }

            $attempt->forceFill([
                'status' => $status,
                'submitted_at' => now(),
            ])->save();

            return $this->examService->gradeAttempt($attempt->fresh());
        });
    }
}
