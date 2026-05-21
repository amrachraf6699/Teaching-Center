<?php

namespace Modules\Academics\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Academics\Database\Factories\GroupSessionFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable(['teaching_group_id', 'title', 'starts_at', 'ends_at', 'notes'])]
class GroupSession extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'teaching_sessions';

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
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
