<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Modules\Academics\Models\TeachingGroup;
use Modules\Academics\Models\Timetable;

uses(RefreshDatabase::class);

it('creates updates and shows a group timetable', function () {
    $teacher = User::factory()->teacher()->create();
    $group = TeachingGroup::create([
        'name' => 'Physics Group',
        'subject' => 'Physics',
    ]);

    $entries = [
        ['day' => 'monday', 'active' => true, 'starts_at' => '15:00', 'ends_at' => '16:30'],
        ['day' => 'tuesday', 'active' => false, 'starts_at' => '', 'ends_at' => ''],
        ['day' => 'wednesday', 'active' => true, 'starts_at' => '17:00', 'ends_at' => '18:00'],
        ['day' => 'thursday', 'active' => false, 'starts_at' => '', 'ends_at' => ''],
        ['day' => 'friday', 'active' => false, 'starts_at' => '', 'ends_at' => ''],
        ['day' => 'saturday', 'active' => false, 'starts_at' => '', 'ends_at' => ''],
        ['day' => 'sunday', 'active' => false, 'starts_at' => '', 'ends_at' => ''],
    ];

    $this->actingAs($teacher)
        ->post(route('admin.timetables.store'), [
            'teaching_group_id' => $group->id,
            'entries' => $entries,
        ])
        ->assertRedirect(route('admin.timetables.index'));

    $timetable = Timetable::query()->where('teaching_group_id', $group->id)->firstOrFail();
    expect($timetable->entries()->count())->toBe(2);

    $this->actingAs($teacher)
        ->get(route('admin.timetables.show', $timetable))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Timetables/Show')
            ->where('timetable.group.name', 'Physics Group')
            ->has('timetable.entries', 2));

    $updatedEntries = [
        ['day' => 'monday', 'active' => false, 'starts_at' => '', 'ends_at' => ''],
        ['day' => 'tuesday', 'active' => true, 'starts_at' => '14:00', 'ends_at' => '15:00'],
        ['day' => 'wednesday', 'active' => true, 'starts_at' => '18:00', 'ends_at' => '19:30'],
        ['day' => 'thursday', 'active' => false, 'starts_at' => '', 'ends_at' => ''],
        ['day' => 'friday', 'active' => false, 'starts_at' => '', 'ends_at' => ''],
        ['day' => 'saturday', 'active' => false, 'starts_at' => '', 'ends_at' => ''],
        ['day' => 'sunday', 'active' => false, 'starts_at' => '', 'ends_at' => ''],
    ];

    $this->actingAs($teacher)
        ->put(route('admin.timetables.update', $timetable), [
            'entries' => $updatedEntries,
        ])
        ->assertRedirect(route('admin.timetables.show', $timetable));

    expect($timetable->refresh()->entries()->count())->toBe(2)
        ->and($timetable->entries()->where('day_of_week', 'tuesday')->exists())->toBeTrue()
        ->and($timetable->entries()->where('day_of_week', 'monday')->exists())->toBeFalse();

    $this->actingAs($teacher)
        ->get(route('admin.groups.show', $group))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Groups/Show')
            ->has('group.timetable.entries', 2));
});

it('enforces one timetable per group', function () {
    $teacher = User::factory()->teacher()->create();
    $group = TeachingGroup::create([
        'name' => 'Math Group',
    ]);

    Timetable::create([
        'teaching_group_id' => $group->id,
    ]);

    $entries = collect(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])
        ->map(fn (string $day): array => [
            'day' => $day,
            'active' => $day === 'monday',
            'starts_at' => $day === 'monday' ? '10:00' : '',
            'ends_at' => $day === 'monday' ? '11:00' : '',
        ])->all();

    $this->actingAs($teacher)
        ->post(route('admin.timetables.store'), [
            'teaching_group_id' => $group->id,
            'entries' => $entries,
        ])
        ->assertSessionHasErrors('teaching_group_id');
});

it('prevents storing a timetable when the day and time overlap another timetable', function () {
    $teacher = User::factory()->teacher()->create();
    $existingGroup = TeachingGroup::create(['name' => 'Existing Group']);
    $newGroup = TeachingGroup::create(['name' => 'New Group']);

    $existingTimetable = Timetable::create([
        'teaching_group_id' => $existingGroup->id,
    ]);
    $existingTimetable->entries()->create([
        'day_of_week' => 'saturday',
        'starts_at' => '10:00',
        'ends_at' => '11:30',
    ]);

    $entries = [
        ['day' => 'monday', 'active' => false, 'starts_at' => '', 'ends_at' => ''],
        ['day' => 'tuesday', 'active' => false, 'starts_at' => '', 'ends_at' => ''],
        ['day' => 'wednesday', 'active' => false, 'starts_at' => '', 'ends_at' => ''],
        ['day' => 'thursday', 'active' => false, 'starts_at' => '', 'ends_at' => ''],
        ['day' => 'friday', 'active' => false, 'starts_at' => '', 'ends_at' => ''],
        ['day' => 'saturday', 'active' => true, 'starts_at' => '11:00', 'ends_at' => '12:00'],
        ['day' => 'sunday', 'active' => false, 'starts_at' => '', 'ends_at' => ''],
    ];

    $this->actingAs($teacher)
        ->from(route('admin.timetables.create'))
        ->post(route('admin.timetables.store'), [
            'teaching_group_id' => $newGroup->id,
            'entries' => $entries,
        ])
        ->assertRedirect(route('admin.timetables.create'))
        ->assertSessionHasErrors('entries');

    expect(Timetable::query()->where('teaching_group_id', $newGroup->id)->exists())->toBeFalse();
});

it('prevents updating a timetable when the day and time overlap another timetable', function () {
    $teacher = User::factory()->teacher()->create();
    $firstGroup = TeachingGroup::create(['name' => 'First Group']);
    $secondGroup = TeachingGroup::create(['name' => 'Second Group']);

    $firstTimetable = Timetable::create([
        'teaching_group_id' => $firstGroup->id,
    ]);
    $firstTimetable->entries()->create([
        'day_of_week' => 'sunday',
        'starts_at' => '09:00',
        'ends_at' => '10:00',
    ]);

    $secondTimetable = Timetable::create([
        'teaching_group_id' => $secondGroup->id,
    ]);
    $secondTimetable->entries()->create([
        'day_of_week' => 'monday',
        'starts_at' => '13:00',
        'ends_at' => '14:00',
    ]);

    $updatedEntries = [
        ['day' => 'monday', 'active' => false, 'starts_at' => '', 'ends_at' => ''],
        ['day' => 'tuesday', 'active' => false, 'starts_at' => '', 'ends_at' => ''],
        ['day' => 'wednesday', 'active' => false, 'starts_at' => '', 'ends_at' => ''],
        ['day' => 'thursday', 'active' => false, 'starts_at' => '', 'ends_at' => ''],
        ['day' => 'friday', 'active' => false, 'starts_at' => '', 'ends_at' => ''],
        ['day' => 'saturday', 'active' => false, 'starts_at' => '', 'ends_at' => ''],
        ['day' => 'sunday', 'active' => true, 'starts_at' => '09:30', 'ends_at' => '10:30'],
    ];

    $this->actingAs($teacher)
        ->from(route('admin.timetables.edit', $secondTimetable))
        ->put(route('admin.timetables.update', $secondTimetable), [
            'entries' => $updatedEntries,
        ])
        ->assertRedirect(route('admin.timetables.edit', $secondTimetable))
        ->assertSessionHasErrors('entries');

    expect($secondTimetable->fresh()->entries()->where('day_of_week', 'monday')->exists())->toBeTrue()
        ->and($secondTimetable->fresh()->entries()->where('day_of_week', 'sunday')->exists())->toBeFalse();
});
