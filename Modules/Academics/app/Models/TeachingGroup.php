<?php

namespace Modules\Academics\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Exams\Models\Exam;
use Modules\Academics\Database\Factories\TeachingGroupFactory;
use Modules\People\Models\Student;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable(['name', 'subject', 'level', 'description', 'is_active'])]
class TeachingGroup extends Model
{
    use HasFactory, LogsActivity;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsToMany<Student, $this>
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'group_student')
            ->withTimestamps();
    }

    /**
     * @return HasMany<GroupSession, $this>
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(GroupSession::class, 'teaching_group_id');
    }

    /**
     * @return HasMany<Exam, $this>
     */
    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class, 'teaching_group_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected static function newFactory(): TeachingGroupFactory
    {
        return TeachingGroupFactory::new();
    }
}
