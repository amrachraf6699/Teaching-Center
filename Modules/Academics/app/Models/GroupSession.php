<?php

namespace Modules\Academics\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Academics\Database\Factories\GroupSessionFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class GroupSession extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'teaching_sessions';

    protected $fillable = ['teaching_group_id', 'source_type', 'timetable_entry_id', 'title', 'starts_at', 'ends_at', 'session_date', 'attendance_entry_enabled', 'manual_attendance_code', 'notes'];

    protected static function booted(): void
    {
        static::saving(function (GroupSession $session): void {
            if (blank($session->source_type)) {
                $session->source_type = 'manual';
            }

            if ($session->starts_at) {
                $session->session_date = $session->starts_at instanceof \DateTimeInterface
                    ? $session->starts_at->format('Y-m-d')
                    : substr((string) $session->starts_at, 0, 10);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'session_date' => 'date',
            'attendance_entry_enabled' => 'boolean',
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
     * @return HasMany<Attendance, $this>
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(Attendance::class, 'teaching_session_id');
    }

    /**
     * @return BelongsTo<TimetableEntry, $this>
     */
    public function timetableEntry(): BelongsTo
    {
        return $this->belongsTo(TimetableEntry::class, 'timetable_entry_id');
    }

    public function isGenerated(): bool
    {
        return $this->source_type === 'timetable';
    }

    public function ensureManualAttendanceCode(): string
    {
        if (filled($this->manual_attendance_code)) {
            return $this->manual_attendance_code;
        }

        $this->forceFill([
            'manual_attendance_code' => $this->generateManualAttendanceCode(),
        ])->saveQuietly();

        return $this->manual_attendance_code;
    }

    public function generateManualAttendanceCode(): string
    {
        do {
            $code = 'SES-'.strtoupper(fake()->bothify('####'));
        } while (static::query()
            ->where('manual_attendance_code', $code)
            ->whereKeyNot($this->getKey())
            ->exists());

        return $code;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected static function newFactory(): GroupSessionFactory
    {
        return GroupSessionFactory::new();
    }
}
