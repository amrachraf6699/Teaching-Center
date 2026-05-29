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
            'name' => $this->faker->words(3, true),
            'subject' => $this->faker->randomElement(['Mathematics', 'Science', 'English']),
            'level' => $this->faker->randomElement(['Grade 8', 'Grade 9', 'Grade 10']),
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}
