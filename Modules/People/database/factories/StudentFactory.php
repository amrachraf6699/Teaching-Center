<?php

namespace Modules\People\Database\Factories;

use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\People\Models\Student;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        $faker = FakerFactory::create();

        return [
            'parent_id' => User::factory()->parent(),

            'name' => $faker->name,

            'code' => strtoupper(sprintf(
                'ST-%s%s-%d%d',
                $faker->randomLetter,
                $faker->randomLetter,
                $faker->numberBetween(0, 9),
                $faker->numberBetween(0, 9)
            )),

            'phone' => $faker->numerify('010########'),

            'date_of_birth' => $faker
                ->dateTimeBetween('-18 years', '-8 years')
                ->format('Y-m-d'),

            'notes' => $faker->optional()->sentence,

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