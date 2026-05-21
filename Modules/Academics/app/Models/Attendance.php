<?php

namespace Modules\Academics\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Academics\Database\Factories\AttendanceFactory;
use Modules\People\Models\Student;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable(['teaching_session_id', 'student_id', 'status', 'notes'])]
class Attendance extends Model
{
    use HasFactory, LogsActivity;

    /**
     * @return BelongsTo<GroupSession, $this>
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(GroupSession::class, 'teaching_session_id');
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected static function newFactory(): AttendanceFactory
    {
        return AttendanceFactory::new();
    }
}
