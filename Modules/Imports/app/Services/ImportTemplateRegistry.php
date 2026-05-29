<?php

namespace Modules\Imports\Services;

class ImportTemplateRegistry
{
    public const TYPES = [
        'parents' => ['name', 'email', 'password'],
        'students' => ['name', 'code', 'parent_email', 'phone', 'date_of_birth', 'notes', 'is_active', 'password'],
        'groups' => ['name', 'subject', 'level', 'description', 'is_active'],
        'enrollments' => ['student_code', 'group_name'],
        'timetables' => ['group_name', 'day_of_week', 'starts_at', 'ends_at'],
        'sessions' => ['group_name', 'title', 'starts_at', 'ends_at', 'attendance_entry_enabled', 'notes'],
        'attendance' => ['session_title', 'session_starts_at', 'student_code', 'status', 'notes'],
        'exams' => ['group_name', 'title', 'start_at', 'end_at', 'max_allowed_time', 'student_review_mode', 'notes'],
        'exam_questions' => ['group_name', 'exam_title', 'exam_start_at', 'question_position', 'question_type', 'prompt', 'points', 'correct_boolean', 'option_position', 'option_label', 'is_correct'],
        'exam_results' => ['group_name', 'exam_title', 'student_code', 'score', 'notes'],
    ];

    public function types(): array
    {
        return self::TYPES;
    }

    public function exists(string $type): bool
    {
        return array_key_exists($type, self::TYPES);
    }

    public function labels(): array
    {
        return [
            'parents' => 'Parents',
            'students' => 'Students',
            'groups' => 'Groups',
            'enrollments' => 'Enrollments',
            'timetables' => 'Timetables',
            'sessions' => 'Sessions',
            'attendance' => 'Attendance',
            'exams' => 'Exams',
            'exam_questions' => 'Exam Questions',
            'exam_results' => 'Exam Results',
        ];
    }

    public function headersFor(string $type): array
    {
        abort_unless(array_key_exists($type, self::TYPES), 404);

        return self::TYPES[$type];
    }

    public function headers(string $type): array
    {
        return $this->headersFor($type);
    }

    public function options(): array
    {
        $labels = $this->labels();

        return collect(self::TYPES)
            ->keys()
            ->map(fn (string $type): array => [
                'value' => $type,
                'label' => $labels[$type],
                'headers' => self::TYPES[$type],
                'template_csv_url' => route('admin.imports.template', ['type' => $type, 'format' => 'csv']),
                'template_xlsx_url' => route('admin.imports.template', ['type' => $type, 'format' => 'xlsx']),
                'store_url' => route('admin.imports.store', $type),
            ])
            ->values()
            ->all();
    }
}
