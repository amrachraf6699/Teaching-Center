<?php

namespace Modules\Exams\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Academics\Models\TeachingGroup;
use Modules\Exams\Models\Exam;

/**
 * @extends Factory<Exam>
 */
class ExamFactory extends Factory
{
    protected $model = Exam::class;

    public function definition(): array
    {
        $startAt = fake()->dateTimeBetween('-1 month', '+1 week');
        $endAt = (clone $startAt)->modify('+1 hour');

        return [
            'teaching_group_id' => TeachingGroup::factory(),
            'title' => fake()->randomElement(['Quiz', 'Midterm', 'Practice Test']).' '.fake()->numberBetween(1, 4),
            'start_at' => $startAt,
            'end_at' => $endAt,
            'max_allowed_time' => 60,
            'max_score' => fake()->randomElement([20, 50, 100]),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function forGroup(TeachingGroup $group): static
    {
        return $this->state(fn (array $attributes): array => [
            'teaching_group_id' => $group->id,
        ]);
    }
}
