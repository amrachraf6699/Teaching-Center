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
        return [
            'teaching_group_id' => TeachingGroup::factory(),
            'title' => fake()->randomElement(['Quiz', 'Midterm', 'Practice Test']).' '.fake()->numberBetween(1, 4),
            'exam_date' => fake()->dateTimeBetween('-1 month', '+1 week')->format('Y-m-d'),
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
