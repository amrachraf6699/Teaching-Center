<?php

namespace Modules\Academics\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Timetable extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['teaching_group_id'];

    /**
     * @return BelongsTo<TeachingGroup, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(TeachingGroup::class, 'teaching_group_id');
    }

    /**
     * @return HasMany<TimetableEntry, $this>
     */
    public function entries(): HasMany
    {
        return $this->hasMany(TimetableEntry::class)->orderByRaw("
            case day_of_week
                when 'monday' then 1
                when 'tuesday' then 2
                when 'wednesday' then 3
                when 'thursday' then 4
                when 'friday' then 5
                when 'saturday' then 6
                when 'sunday' then 7
                else 8
            end
        ");
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
