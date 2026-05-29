<?php

namespace Modules\Exams\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ExamQuestion extends Model
{
    use HasFactory;

    protected $fillable = ['exam_id', 'type', 'prompt', 'points', 'position'];

    protected function casts(): array
    {
        return [
            'points' => 'decimal:2',
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
     * @return HasMany<ExamQuestionOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(ExamQuestionOption::class)->orderBy('position');
    }

    /**
     * @return HasOne<ExamQuestionOption, $this>
     */
    public function correctOption(): HasOne
    {
        return $this->hasOne(ExamQuestionOption::class)->where('is_correct', true);
    }
}
