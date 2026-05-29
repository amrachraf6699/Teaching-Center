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
        $startAt = $this->faker->dateTimeBetween('-1 month', '+1 week');
        $endAt = (clone $startAt)->modify('+1 hour');

        return [
            'teaching_group_id' => TeachingGroup::factory(),
            'title' => $this->faker->randomElement(['Quiz', 'Midterm', 'Practice Test']).' '.$this->faker->numberBetween(1, 4),
            'start_at' => $startAt,
            'end_at' => $endAt,
            'max_allowed_time' => 60,
            'max_score' => $this->faker->randomElement([20, 50, 100]),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function forGroup(TeachingGroup $group): static
    {
        return $this->state(fn (array $attributes): array => [
            'teaching_group_id' => $group->id,
        ]);
    }
}
