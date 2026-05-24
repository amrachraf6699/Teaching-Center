<?php

namespace Modules\Exams\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['exam_question_id', 'label', 'is_correct', 'position'])]
class ExamQuestionOption extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<ExamQuestion, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(ExamQuestion::class, 'exam_question_id');
    }
}
