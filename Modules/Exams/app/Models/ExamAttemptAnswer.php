<?php

namespace Modules\Exams\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAttemptAnswer extends Model
{
    use HasFactory;

    protected $fillable = ['exam_attempt_id', 'exam_question_id', 'selected_option_id', 'is_correct', 'earned_points'];

    protected function casts(): array
    {
        return [
            'selected_option_id' => 'integer',
            'is_correct' => 'boolean',
            'earned_points' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<ExamAttempt, $this>
     */
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'exam_attempt_id');
    }

    /**
     * @return BelongsTo<ExamQuestion, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(ExamQuestion::class, 'exam_question_id');
    }

    /**
     * @return BelongsTo<ExamQuestionOption, $this>
     */
    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(ExamQuestionOption::class, 'selected_option_id');
    }
}
