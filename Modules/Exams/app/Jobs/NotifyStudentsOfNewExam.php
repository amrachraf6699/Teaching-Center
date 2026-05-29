<?php

namespace Modules\Exams\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Modules\Exams\Models\Exam;
use Modules\Notifications\Services\PortalNotificationService;

class NotifyStudentsOfNewExam implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $examId) {}

    public function handle(PortalNotificationService $notifications): void
    {
        $exam = Exam::query()
            ->with('group.students.user')
            ->find($this->examId);

        if (! $exam || ! $exam->group) {
            return;
        }

        foreach ($exam->group->students as $student) {
            $notifications->createOnceForAudience(
                $student,
                'student',
                'exam_created',
                $this->referenceKey($exam->id, $student->id),
                'New exam available',
                sprintf(
                    '%s has been scheduled for %s.',
                    $exam->title,
                    $exam->start_at?->format('M j, Y g:i A') ?? 'an upcoming time'
                ),
            );
        }
    }

    private function referenceKey(int $examId, int $studentId): string
    {
        return sprintf('exam:%d:student:%d:created', $examId, $studentId);
    }
}
