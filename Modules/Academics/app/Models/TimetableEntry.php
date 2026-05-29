<?php

namespace Modules\Academics\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimetableEntry extends Model
{
    use HasFactory;

    protected $fillable = ['timetable_id', 'day_of_week', 'starts_at', 'ends_at'];

    /**
     * @return BelongsTo<Timetable, $this>
     */
    public function timetable(): BelongsTo
    {
        return $this->belongsTo(Timetable::class);
    }

    /**
     * @return HasMany<GroupSession, $this>
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(GroupSession::class, 'timetable_entry_id');
    }
}
