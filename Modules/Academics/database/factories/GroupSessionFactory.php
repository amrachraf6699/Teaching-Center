<?php

namespace Modules\Academics\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Academics\Models\GroupSession;
use Modules\Academics\Models\TeachingGroup;

/**
 * @extends Factory<GroupSession>
 */
class GroupSessionFactory extends Factory
{
    protected $model = GroupSession::class;

    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('-1 week', '+1 week');

        return [
            'teaching_group_id' => TeachingGroup::factory(),
            'source_type' => 'manual',
            'title' => fake()->sentence(3),
            'starts_at' => $startsAt,
            'ends_at' => (clone $startsAt)->modify('+90 minutes'),
            'session_date' => $startsAt->format('Y-m-d'),
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
