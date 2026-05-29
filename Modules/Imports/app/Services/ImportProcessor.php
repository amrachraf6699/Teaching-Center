<?php

namespace Modules\Imports\Services;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Academics\Actions\GenerateSessionsFromTimetables;
use Modules\Academics\Models\Attendance;
use Modules\Academics\Models\GroupSession;
use Modules\Academics\Models\TeachingGroup;
use Modules\Academics\Models\Timetable;
use Modules\Academics\Models\TimetableEntry;
use Modules\Exams\Models\Exam;
use Modules\Exams\Models\ExamQuestion;
use Modules\Exams\Models\ExamQuestionOption;
use Modules\Exams\Models\ExamResult;
use Modules\Imports\Models\ImportBatch;
use Modules\Imports\Support\ImportRows;
use Modules\People\Models\Student;
use Throwable;

class ImportProcessor
{
    /**
     * @var array<int, string>
     */
    private array $weekdays = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

    public function __construct(
        private readonly ImportTemplateRegistry $templates,
        private readonly GenerateSessionsFromTimetables $generateSessions,
    ) {}

    public function process(UploadedFile $file, string $type, User $user): ImportBatch
    {
        abort_unless($this->templates->exists($type), 404);

        $batch = ImportBatch::query()->create([
            'user_id' => $user->id,
            'type' => $type,
            'file_name' => $file->getClientOriginalName(),
            'status' => 'processing',
            'total_rows' => 0,
            'imported_rows' => 0,
            'failed_rows' => 0,
            'errors' => [],
        ]);

        try {
            $import = new ImportRows;
            Excel::import($import, $file);

            $rows = $this->nonBlankRows($import->rows);
            $this->assertHeaders($rows, $type);

            $errors = [];
            $imported = 0;

            foreach ($rows->values() as $index => $row) {
                $rowNumber = $index + 2;

                try {
                    DB::transaction(fn () => $this->importRow($type, $row));
                    $imported++;
                } catch (Throwable $exception) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'field' => $this->errorField($exception),
                        'message' => $exception->getMessage(),
                    ];
                }
            }

            $batch->forceFill([
                'status' => $errors === [] ? 'completed' : 'completed_with_errors',
                'total_rows' => $rows->count(),
                'imported_rows' => $imported,
                'failed_rows' => count($errors),
                'errors' => $errors,
            ])->save();
        } catch (Throwable $exception) {
            $batch->forceFill([
                'status' => 'failed',
                'errors' => [[
                    'row' => null,
                    'field' => null,
                    'message' => $exception->getMessage(),
                ]],
            ])->save();
        }

        return $batch->fresh();
    }

    /**
     * @param  Collection<int, Collection<string, mixed>>  $rows
     * @return Collection<int, Collection<string, mixed>>
     */
    private function nonBlankRows(Collection $rows): Collection
    {
        return $rows->filter(fn (Collection $row): bool => $row
            ->filter(fn (mixed $value): bool => filled($value))
            ->isNotEmpty());
    }

    /**
     * @param  Collection<int, Collection<string, mixed>>  $rows
     */
    private function assertHeaders(Collection $rows, string $type): void
    {
        if ($rows->isEmpty()) {
            throw ValidationException::withMessages(['file' => 'The import file must contain at least one data row.']);
        }

        $firstRow = $rows->first();
        $headers = $firstRow instanceof Collection ? $firstRow->keys()->all() : [];
        $missing = array_values(array_diff($this->templates->headers($type), $headers));

        if ($missing !== []) {
            throw ValidationException::withMessages([
                'headers' => 'Missing required headers: '.implode(', ', $missing),
            ]);
        }
    }

    /**
     * @param  Collection<string, mixed>  $row
     */
    private function importRow(string $type, Collection $row): void
    {
        match ($type) {
            'parents' => $this->importParent($row),
            'students' => $this->importStudent($row),
            'groups' => $this->importGroup($row),
            'enrollments' => $this->importEnrollment($row),
            'timetables' => $this->importTimetable($row),
            'sessions' => $this->importSession($row),
            'attendance' => $this->importAttendance($row),
            'exams' => $this->importExam($row),
            'exam_questions' => $this->importExamQuestion($row),
            'exam_results' => $this->importExamResult($row),
        };
    }

    /**
     * @param  Collection<string, mixed>  $row
     */
    private function importParent(Collection $row): void
    {
        $email = strtolower($this->required($row, 'email'));
        $existing = User::query()->where('email', $email)->first();

        if ($existing && $existing->role !== 'parent') {
            throw ValidationException::withMessages(['email' => 'This email already belongs to a non-parent account.']);
        }

        $attributes = [
            'name' => $this->required($row, 'name'),
            'role' => 'parent',
        ];

        if (filled($this->value($row, 'password')) || ! $existing) {
            $attributes['password'] = Hash::make($this->value($row, 'password') ?: 'password');
        }

        User::query()->updateOrCreate(['email' => $email], $attributes);
    }

    /**
     * @param  Collection<string, mixed>  $row
     */
    private function importStudent(Collection $row): void
    {
        $parent = $this->parentByEmail($this->required($row, 'parent_email'), true);
        $code = $this->value($row, 'code');
        $lookup = filled($code)
            ? ['code' => $code]
            : ['name' => $this->required($row, 'name'), 'parent_id' => $parent->id];

        $student = Student::query()->firstOrNew($lookup);
        $student->fill([
            'parent_id' => $parent->id,
            'name' => $this->required($row, 'name'),
            'code' => $code ?: $student->code,
            'phone' => $this->value($row, 'phone'),
            'date_of_birth' => $this->nullableDate($row, 'date_of_birth'),
            'notes' => $this->value($row, 'notes'),
            'is_active' => $this->bool($row, 'is_active', true),
        ]);
        $student->save();

        if (filled($this->value($row, 'password')) && $student->user) {
            $student->user->forceFill([
                'password' => Hash::make($this->value($row, 'password')),
            ])->save();
        }
    }

    /**
     * @param  Collection<string, mixed>  $row
     */
    private function importGroup(Collection $row): void
    {
        TeachingGroup::query()->updateOrCreate(
            ['name' => $this->required($row, 'name')],
            [
                'subject' => $this->value($row, 'subject'),
                'level' => $this->value($row, 'level'),
                'description' => $this->value($row, 'description'),
                'is_active' => $this->bool($row, 'is_active', true),
            ],
        );
    }

    /**
     * @param  Collection<string, mixed>  $row
     */
    private function importEnrollment(Collection $row): void
    {
        $student = $this->studentByCode($this->required($row, 'student_code'));
        $group = $this->groupByName($this->required($row, 'group_name'));

        $group->students()->syncWithoutDetaching([$student->id]);
    }

    /**
     * @param  Collection<string, mixed>  $row
     */
    private function importTimetable(Collection $row): void
    {
        $group = $this->groupByName($this->required($row, 'group_name'));
        $day = strtolower($this->required($row, 'day_of_week'));

        if (! in_array($day, $this->weekdays, true)) {
            throw ValidationException::withMessages(['day_of_week' => 'The day_of_week value is invalid.']);
        }

        $startsAt = $this->requiredTime($row, 'starts_at');
        $endsAt = $this->requiredTime($row, 'ends_at');

        if ($startsAt >= $endsAt) {
            throw ValidationException::withMessages(['ends_at' => 'The end time must be after the start time.']);
        }

        $timetable = Timetable::query()->firstOrCreate(['teaching_group_id' => $group->id]);
        $entry = TimetableEntry::query()->firstOrNew([
            'timetable_id' => $timetable->id,
            'day_of_week' => $day,
        ]);

        $conflict = TimetableEntry::query()
            ->whereHas('timetable', fn ($query) => $query->where('teaching_group_id', $group->id))
            ->where('day_of_week', $day)
            ->when($entry->exists, fn ($query) => $query->whereKeyNot($entry->id))
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages(['starts_at' => 'This timetable entry overlaps an existing entry for the group.']);
        }

        $entry->fill([
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ])->save();

        $this->generateSessions->handle($timetable->fresh(['group', 'entries']), now(), 7);
    }

    /**
     * @param  Collection<string, mixed>  $row
     */
    private function importSession(Collection $row): void
    {
        $group = $this->groupByName($this->required($row, 'group_name'));
        $startsAt = $this->requiredDateTime($row, 'starts_at');
        $session = GroupSession::query()->firstOrNew([
            'teaching_group_id' => $group->id,
            'title' => $this->required($row, 'title'),
            'starts_at' => $startsAt,
        ]);

        $session->fill([
            'source_type' => $session->source_type ?: 'manual',
            'ends_at' => $this->nullableDateTime($row, 'ends_at'),
            'attendance_entry_enabled' => $this->bool($row, 'attendance_entry_enabled', true),
            'notes' => $this->value($row, 'notes'),
        ])->save();
        $session->ensureManualAttendanceCode();
    }

    /**
     * @param  Collection<string, mixed>  $row
     */
    private function importAttendance(Collection $row): void
    {
        $session = $this->sessionByTitleAndStart($this->required($row, 'session_title'), $this->requiredDateTime($row, 'session_starts_at'));
        $student = $this->studentByCode($this->required($row, 'student_code'));

        if (! $session->group->students()->whereKey($student->id)->exists()) {
            throw ValidationException::withMessages(['student_code' => 'The student is not enrolled in the session group.']);
        }

        $status = strtolower($this->value($row, 'status') ?: 'present');

        if (! in_array($status, ['present', 'absent', 'late', 'excused'], true)) {
            throw ValidationException::withMessages(['status' => 'The attendance status is invalid.']);
        }

        Attendance::query()->updateOrCreate(
            ['teaching_session_id' => $session->id, 'student_id' => $student->id],
            ['status' => $status, 'notes' => $this->value($row, 'notes')],
        );
    }

    /**
     * @param  Collection<string, mixed>  $row
     */
    private function importExam(Collection $row): void
    {
        $group = $this->groupByName($this->required($row, 'group_name'));
        $exam = Exam::query()->firstOrNew([
            'teaching_group_id' => $group->id,
            'title' => $this->required($row, 'title'),
            'start_at' => $this->requiredDateTime($row, 'start_at'),
        ]);

        $reviewMode = $this->value($row, 'student_review_mode') ?: 'score_only';

        if (! in_array($reviewMode, ['score_only', 'question_review'], true)) {
            throw ValidationException::withMessages(['student_review_mode' => 'The student review mode is invalid.']);
        }

        $exam->fill([
            'end_at' => $this->requiredDateTime($row, 'end_at'),
            'max_allowed_time' => $this->requiredInt($row, 'max_allowed_time', min: 1),
            'max_score' => $exam->exists ? $exam->max_score : 100,
            'student_review_mode' => $reviewMode,
            'notes' => $this->value($row, 'notes'),
        ])->save();
    }

    /**
     * @param  Collection<string, mixed>  $row
     */
    private function importExamQuestion(Collection $row): void
    {
        $exam = $this->examByGroupAndTitle(
            $this->required($row, 'group_name'),
            $this->required($row, 'exam_title'),
            $this->nullableDateTime($row, 'exam_start_at'),
        );
        $position = $this->requiredInt($row, 'question_position', min: 1);
        $type = strtolower($this->required($row, 'question_type'));

        if (! in_array($type, ['mcq', 'true_false'], true)) {
            throw ValidationException::withMessages(['question_type' => 'The question type is invalid.']);
        }

        $question = ExamQuestion::query()->updateOrCreate(
            ['exam_id' => $exam->id, 'position' => $position],
            [
                'type' => $type,
                'prompt' => $this->required($row, 'prompt'),
                'points' => $this->requiredDecimal($row, 'points', min: 0.01),
            ],
        );

        if ($type === 'true_false') {
            $correct = $this->bool($row, 'correct_boolean', false);
            ExamQuestionOption::query()->updateOrCreate(
                ['exam_question_id' => $question->id, 'position' => 1],
                ['label' => 'True', 'is_correct' => $correct],
            );
            ExamQuestionOption::query()->updateOrCreate(
                ['exam_question_id' => $question->id, 'position' => 2],
                ['label' => 'False', 'is_correct' => ! $correct],
            );
        } else {
            ExamQuestionOption::query()->updateOrCreate(
                [
                    'exam_question_id' => $question->id,
                    'position' => $this->requiredInt($row, 'option_position', min: 1),
                ],
                [
                    'label' => $this->required($row, 'option_label'),
                    'is_correct' => $this->bool($row, 'is_correct', false),
                ],
            );
        }

        $exam->recalculateMaxScore();
        $exam->save();
    }

    /**
     * @param  Collection<string, mixed>  $row
     */
    private function importExamResult(Collection $row): void
    {
        $exam = $this->examByGroupAndTitle($this->required($row, 'group_name'), $this->required($row, 'exam_title'));
        $student = $this->studentByCode($this->required($row, 'student_code'));

        if (! $exam->group->students()->whereKey($student->id)->exists()) {
            throw ValidationException::withMessages(['student_code' => 'The student is not enrolled in the exam group.']);
        }

        $score = $this->requiredDecimal($row, 'score', min: 0);

        if ($score > (float) $exam->max_score) {
            throw ValidationException::withMessages(['score' => 'The score cannot exceed the exam maximum score.']);
        }

        ExamResult::query()->updateOrCreate(
            ['exam_id' => $exam->id, 'student_id' => $student->id],
            ['score' => $score, 'notes' => $this->value($row, 'notes')],
        );
    }

    /**
     * @param  Collection<string, mixed>  $row
     */
    private function value(Collection $row, string $key): ?string
    {
        $value = $row->get($key);

        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * @param  Collection<string, mixed>  $row
     */
    private function required(Collection $row, string $key): string
    {
        $value = $this->value($row, $key);

        if ($value === null) {
            throw ValidationException::withMessages([$key => "The {$key} field is required."]);
        }

        return $value;
    }

    /**
     * @param  Collection<string, mixed>  $row
     */
    private function bool(Collection $row, string $key, bool $default): bool
    {
        $value = $this->value($row, $key);

        if ($value === null) {
            return $default;
        }

        return match (strtolower($value)) {
            '1', 'true', 'yes', 'y', 'enabled', 'active', 'present' => true,
            '0', 'false', 'no', 'n', 'disabled', 'inactive', 'absent' => false,
            default => throw ValidationException::withMessages([$key => "The {$key} field must be a boolean value."]),
        };
    }

    /**
     * @param  Collection<string, mixed>  $row
     */
    private function nullableDate(Collection $row, string $key): ?string
    {
        $value = $this->value($row, $key);

        return $value ? CarbonImmutable::parse($value)->toDateString() : null;
    }

    /**
     * @param  Collection<string, mixed>  $row
     */
    private function nullableDateTime(Collection $row, string $key): ?CarbonImmutable
    {
        $value = $this->value($row, $key);

        return $value ? CarbonImmutable::parse($value) : null;
    }

    /**
     * @param  Collection<string, mixed>  $row
     */
    private function requiredDateTime(Collection $row, string $key): CarbonImmutable
    {
        return CarbonImmutable::parse($this->required($row, $key));
    }

    /**
     * @param  Collection<string, mixed>  $row
     */
    private function requiredTime(Collection $row, string $key): string
    {
        return CarbonImmutable::parse($this->required($row, $key))->format('H:i:s');
    }

    /**
     * @param  Collection<string, mixed>  $row
     */
    private function requiredInt(Collection $row, string $key, int $min = PHP_INT_MIN): int
    {
        $value = filter_var($this->required($row, $key), FILTER_VALIDATE_INT);

        if ($value === false || $value < $min) {
            throw ValidationException::withMessages([$key => "The {$key} field is invalid."]);
        }

        return $value;
    }

    /**
     * @param  Collection<string, mixed>  $row
     */
    private function requiredDecimal(Collection $row, string $key, float $min): float
    {
        $value = filter_var($this->required($row, $key), FILTER_VALIDATE_FLOAT);

        if ($value === false || $value < $min) {
            throw ValidationException::withMessages([$key => "The {$key} field is invalid."]);
        }

        return $value;
    }

    private function parentByEmail(string $email, bool $createIfMissing = false): User
    {
        $email = strtolower($email);
        $parent = User::query()->where('email', $email)->where('role', 'parent')->first();

        if ($parent || ! $createIfMissing) {
            return $parent ?? throw ValidationException::withMessages(['parent_email' => 'The parent email was not found.']);
        }

        return User::query()->create([
            'name' => ucfirst(strtok($email, '@') ?: 'Parent'),
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => 'parent',
        ]);
    }

    private function studentByCode(string $code): Student
    {
        return Student::query()->where('code', $code)->first()
            ?? throw ValidationException::withMessages(['student_code' => 'The student code was not found.']);
    }

    private function groupByName(string $name): TeachingGroup
    {
        return TeachingGroup::query()->where('name', $name)->first()
            ?? throw ValidationException::withMessages(['group_name' => 'The group name was not found.']);
    }

    private function sessionByTitleAndStart(string $title, CarbonImmutable $startsAt): GroupSession
    {
        return GroupSession::query()
            ->where('title', $title)
            ->where('starts_at', $startsAt)
            ->first()
            ?? throw ValidationException::withMessages(['session_title' => 'The session was not found.']);
    }

    private function examByGroupAndTitle(string $groupName, string $title, ?CarbonImmutable $startsAt = null): Exam
    {
        $group = $this->groupByName($groupName);

        return Exam::query()
            ->where('teaching_group_id', $group->id)
            ->where('title', $title)
            ->when($startsAt, fn ($query) => $query->where('start_at', $startsAt))
            ->first()
            ?? throw ValidationException::withMessages(['exam_title' => 'The exam was not found.']);
    }

    private function errorField(Throwable $exception): ?string
    {
        if ($exception instanceof ValidationException) {
            return array_key_first($exception->errors());
        }

        return null;
    }
}
