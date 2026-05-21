<?php

namespace Modules\Exams\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Exams\Database\Factories\ExamResultFactory;
use Modules\People\Models\Student;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable(['exam_id', 'student_id', 'score', 'notes'])]
class ExamResult extends Model
{
    use HasFactory, LogsActivity;

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Exam, $this>
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function percentage(): float
    {
        $maxScore = (float) $this->exam->max_score;

        if ($maxScore <= 0) {
            return 0.0;
        }

        return round(((float) $this->score / $maxScore) * 100, 2);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected static function newFactory(): ExamResultFactory
    {
        return ExamResultFactory::new();
    }
}
