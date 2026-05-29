<?php

namespace Modules\Exams\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\People\Models\Student;

class ExamAttempt extends Model
{
    use HasFactory;

    protected $fillable = ['exam_id', 'student_id', 'started_at', 'submitted_at', 'expires_at', 'status'];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'expires_at' => 'datetime',
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

    /**
     * @return HasMany<ExamAttemptAnswer, $this>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(ExamAttemptAnswer::class);
    }

    public function isSubmitted(): bool
    {
        return in_array($this->status, ['submitted', 'auto_submitted', 'expired'], true);
    }
}
