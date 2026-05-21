<?php

namespace Modules\Exams\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Exams\Models\Exam;
use Modules\Exams\Models\ExamResult;
use Modules\People\Models\Student;

/**
 * @extends Factory<ExamResult>
 */
class ExamResultFactory extends Factory
{
    protected $model = ExamResult::class;

    public function definition(): array
    {
        return [
            'exam_id' => Exam::factory(),
            'student_id' => Student::factory(),
            'score' => fake()->randomFloat(2, 0, 100),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function forExamAndStudent(Exam $exam, Student $student): static
    {
        return $this->state(fn (array $attributes): array => [
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'score' => fake()->randomFloat(2, 0, (float) $exam->max_score),
        ]);
    }
}
