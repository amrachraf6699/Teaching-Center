<?php

namespace Modules\Academics\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Academics\Models\TeachingGroup;

/**
 * @extends Factory<TeachingGroup>
 */
class TeachingGroupFactory extends Factory
{
    protected $model = TeachingGroup::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'subject' => fake()->randomElement(['Mathematics', 'Science', 'English']),
            'level' => fake()->randomElement(['Grade 8', 'Grade 9', 'Grade 10']),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
