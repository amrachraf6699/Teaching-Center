<?php

namespace Modules\Notifications\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Notifications\Models\ParentNotification;
use Modules\People\Models\Student;

/**
 * @extends Factory<ParentNotification>
 */
class ParentNotificationFactory extends Factory
{
    protected $model = ParentNotification::class;

    public function definition(): array
    {
        return [
            'parent_id' => User::factory()->parent(),
            'student_id' => Student::factory(),
            'type' => fake()->randomElement(['general', 'attendance', 'exam_result']),
            'title' => fake()->sentence(3),
            'body' => fake()->paragraph(),
            'read_at' => null,
            'emailed_at' => fake()->optional()->dateTimeBetween('-1 week', 'now'),
        ];
    }

    public function forStudent(Student $student): static
    {
        return $this->state(fn (array $attributes): array => [
            'parent_id' => $student->parent_id,
            'student_id' => $student->id,
        ]);
    }
}
