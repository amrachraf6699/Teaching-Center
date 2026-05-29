<?php

namespace Modules\Exams\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Academics\Models\TeachingGroup;
use Modules\Exams\Database\Factories\ExamFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Exam extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['teaching_group_id', 'title', 'start_at', 'end_at', 'max_allowed_time', 'max_score', 'notes', 'student_review_mode'];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'max_allowed_time' => 'integer',
            'max_score' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<TeachingGroup, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(TeachingGroup::class, 'teaching_group_id');
    }

    /**
     * @return HasMany<ExamResult, $this>
     */
    public function results(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }

    /**
     * @return HasMany<ExamQuestion, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('position');
    }

    /**
     * @return HasMany<ExamAttempt, $this>
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function recalculateMaxScore(): void
    {
        $this->max_score = (float) $this->questions()->sum('points');
    }

    public function canStudentReviewQuestions(): bool
    {
        return $this->student_review_mode === 'question_review';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected static function newFactory(): ExamFactory
    {
        return ExamFactory::new();
    }
}
