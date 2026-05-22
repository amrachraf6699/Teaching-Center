<?php

namespace Modules\People\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Academics\Models\Attendance;
use Modules\Academics\Models\TeachingGroup;
use Modules\Exams\Models\ExamResult;
use Modules\Notifications\Models\ParentNotification;
use Modules\People\Database\Factories\StudentFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable(['parent_id', 'name', 'code', 'phone', 'date_of_birth', 'notes', 'is_active'])]
class Student extends Model
{
    use HasFactory, LogsActivity;

    protected static function booted(): void
    {
        static::creating(function (Student $student): void {
            if (blank($student->code)) {
                $student->code = static::nextCode();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    /**
     * @return BelongsToMany<TeachingGroup, $this>
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(TeachingGroup::class, 'group_student')
            ->withTimestamps();
    }

    /**
     * @return HasMany<Attendance, $this>
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * @return HasMany<ExamResult, $this>
     */
    public function examResults(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }

    /**
     * @return HasMany<ParentNotification, $this>
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(ParentNotification::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public static function nextCode(): string
    {
        $nextNumber = static::query()
            ->pluck('code')
            ->filter()
            ->map(function (string $code): int {
                preg_match('/^ST-(\d+)$/', $code, $matches);

                return (int) ($matches[1] ?? 0);
            })
            ->max() + 1;

        return 'ST-'.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }

    protected static function newFactory(): StudentFactory
    {
        return StudentFactory::new();
    }
}
