<?php

namespace Modules\Exams\Actions;

use Modules\Exams\Jobs\NotifyStudentsOfExamReminder;
use Modules\Exams\Models\Exam;

class DispatchScheduledExamReminders
{
    public function handle(): void
    {
        $this->dispatchForWindow('day', now()->addDay());
        $this->dispatchForWindow('hour', now()->addHour());
    }

    private function dispatchForWindow(string $window, \Illuminate\Support\Carbon $target): void
    {
        $from = $target->copy()->startOfMinute();
        $to = $target->copy()->endOfMinute();

        Exam::query()
            ->whereBetween('start_at', [$from, $to])
            ->pluck('id')
            ->each(fn (int $examId) => NotifyStudentsOfExamReminder::dispatch($examId, $window));
    }
}
