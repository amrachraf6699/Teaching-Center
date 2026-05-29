<?php

namespace Modules\People\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\People\Models\Student;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'parent_id' => User::factory()->parent(),
            'name' => $this->faker->name(),
            'code' => strtoupper(sprintf(
                'ST-%s%s-%d%d',
                $this->faker->randomLetter(),
                $this->faker->randomLetter(),
                $this->faker->numberBetween(0, 9),
                $this->faker->numberBetween(0, 9),
            )),
            'phone' => $this->faker->numerify('010########'),
            'date_of_birth' => $this->faker->dateTimeBetween('-18 years', '-8 years')->format('Y-m-d'),
            'notes' => $this->faker->optional()->sentence(),
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
