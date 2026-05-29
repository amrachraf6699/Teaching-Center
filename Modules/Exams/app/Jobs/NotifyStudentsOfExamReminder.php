<?php

namespace Modules\Exams\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Modules\Exams\Models\Exam;
use Modules\Notifications\Services\PortalNotificationService;

class NotifyStudentsOfExamReminder implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $examId,
        public readonly string $window,
    ) {}

    public function handle(PortalNotificationService $notifications): void
    {
        $exam = Exam::query()
            ->with('group.students.user')
            ->find($this->examId);

        if (! $exam || ! $exam->group || ! in_array($this->window, ['day', 'hour'], true)) {
            return;
        }

        [$title, $bodySuffix] = $this->messageParts($exam);

        foreach ($exam->group->students as $student) {
            $notifications->createOnceForAudience(
                $student,
                'student',
                'exam_reminder',
                $this->referenceKey($exam->id, $student->id),
                $title,
                sprintf(
                    '%s starts at %s. %s',
                    $exam->title,
                    $exam->start_at?->format('M j, Y g:i A') ?? 'the scheduled time',
                    $bodySuffix
                ),
            );
        }
    }

    private function messageParts(Exam $exam): array
    {
        return match ($this->window) {
            'day' => ['Exam tomorrow', 'Be ready one day in advance.'],
            'hour' => ['Exam in one hour', 'Your exam window opens soon.'],
        };
    }

    private function referenceKey(int $examId, int $studentId): string
    {
        return sprintf('exam:%d:student:%d:reminder:%s', $examId, $studentId, $this->window);
    }
}
