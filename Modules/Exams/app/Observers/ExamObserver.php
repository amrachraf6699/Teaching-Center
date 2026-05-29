<?php

namespace Modules\Exams\Observers;

use Modules\Exams\Jobs\NotifyStudentsOfNewExam;
use Modules\Exams\Models\Exam;

class ExamObserver
{
    public function created(Exam $exam): void
    {
        NotifyStudentsOfNewExam::dispatch($exam->id);
    }
}
