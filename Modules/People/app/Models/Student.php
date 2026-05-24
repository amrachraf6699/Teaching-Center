<?php

namespace Modules\People\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;
use Modules\Academics\Models\Attendance;
use Modules\Academics\Models\TeachingGroup;
use Modules\Exams\Models\ExamResult;
use Modules\Notifications\Models\ParentNotification;
use Modules\People\Database\Factories\StudentFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable(['parent_id', 'user_id', 'name', 'code', 'phone', 'date_of_birth', 'notes', 'is_active'])]
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

        static::created(function (Student $student): void {
            if ($student->user_id) {
                return;
            }

            $user = User::query()->create([
                'name' => $student->name,
                'email' => sprintf('student-%d@students.teachify.local', $student->id),
                'password' => Hash::make($student->code ?: 'password'),
                'role' => 'student',
            ]);

            $student->forceFill([
                'user_id' => $user->id,
            ])->saveQuietly();
        });

        static::updated(function (Student $student): void {
            if (! $student->user) {
                return;
            }

            if ($student->wasChanged('name')) {
                $student->user->forceFill([
                    'name' => $student->name,
                ])->saveQuietly();
            }
        });

        static::deleted(function (Student $student): void {
            if ($student->user) {
                $student->user->delete();
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
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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
        do {
            $code = sprintf(
                'ST-%s%s-%d%d',
                fake()->randomLetter(),
                fake()->randomLetter(),
                fake()->numberBetween(0, 9),
                fake()->numberBetween(0, 9),
            );
            $code = strtoupper($code);
        } while (static::query()->where('code', $code)->exists());

        return $code;
    }

    protected static function newFactory(): StudentFactory
    {
        return StudentFactory::new();
    }
}
