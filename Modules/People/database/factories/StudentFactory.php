<?php

namespace Modules\People\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\People\Models\Student;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'parent_id' => User::factory()->parent(),
            'name' => \fake()->name(),
            'code' => strtoupper(sprintf(
                'ST-%s%s-%d%d',
                \fake()->randomLetter(),
                \fake()->randomLetter(),
                \fake()->numberBetween(0, 9),
                \fake()->numberBetween(0, 9),
            )),
            'phone' => \fake()->numerify('010########'),
            'date_of_birth' => \fake()->dateTimeBetween('-18 years', '-8 years')->format('Y-m-d'),
            'notes' => \fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }

    public function forParent(User $parent): static
    {
        return $this->state(fn (array $attributes): array => [
            'parent_id' => $parent->id,
        ]);
    }
}