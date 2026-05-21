<?php

namespace Modules\Academics\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Academics\Models\Attendance;
use Modules\Academics\Models\GroupSession;
use Modules\People\Models\Student;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'teaching_session_id' => GroupSession::factory(),
            'student_id' => Student::factory(),
            'status' => fake()->randomElement(['present', 'present', 'present', 'late', 'absent', 'excused']),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function forSessionAndStudent(GroupSession $session, Student $student): static
    {
        return $this->state(fn (array $attributes): array => [
            'teaching_session_id' => $session->id,
            'student_id' => $student->id,
        ]);
    }
}
