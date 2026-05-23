<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Modules\Academics\Models\TeachingGroup;
use Modules\People\Models\Student;

uses(RefreshDatabase::class);

it('filters the student index by parent, group, status, and search text', function () {
    $teacher = User::factory()->teacher()->create();

    $targetParent = User::factory()->parent()->create([
        'name' => 'Mona Parent',
        'email' => 'mona.parent@example.test',
    ]);
    $otherParent = User::factory()->parent()->create([
        'name' => 'Karim Parent',
        'email' => 'karim.parent@example.test',
    ]);

    $targetGroup = TeachingGroup::create([
        'name' => 'Science Elite',
        'subject' => 'Science',
        'is_active' => true,
    ]);
    $otherGroup = TeachingGroup::create([
        'name' => 'Math Base',
        'subject' => 'Math',
        'is_active' => true,
    ]);

    $matchingStudent = Student::create([
        'parent_id' => $targetParent->id,
        'name' => 'Omar Search',
        'code' => 'ST-301',
        'phone' => '01055555555',
        'is_active' => true,
    ]);
    $matchingStudent->groups()->sync([$targetGroup->id]);

    $otherStudent = Student::create([
        'parent_id' => $otherParent->id,
        'name' => 'Hidden Student',
        'code' => 'ST-302',
        'phone' => '01044444444',
        'is_active' => false,
    ]);
    $otherStudent->groups()->sync([$otherGroup->id]);

    $this->actingAs($teacher)
        ->get(route('admin.students.index', [
            'search' => 'Omar',
            'status' => 'active',
            'parent_id' => $targetParent->id,
            'group_id' => $targetGroup->id,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Students/Index')
            ->where('filters.search', 'Omar')
            ->where('filters.status', 'active')
            ->where('filters.parent_id', (string) $targetParent->id)
            ->where('filters.group_id', (string) $targetGroup->id)
            ->has('parentOptions', 2)
            ->has('groupOptions', 2)
            ->has('students.data', 1)
            ->where('students.data.0.name', 'Omar Search'));
});

it('toggles a student status from the index endpoint', function () {
    $teacher = User::factory()->teacher()->create();
    $parent = User::factory()->parent()->create();
    $student = Student::create([
        'parent_id' => $parent->id,
        'name' => 'Toggle Student',
        'code' => 'ST-401',
        'is_active' => true,
    ]);

    $this->actingAs($teacher)
        ->patch(route('admin.students.toggle-status', $student), [
            'is_active' => false,
        ])
        ->assertRedirect();

    expect($student->refresh()->is_active)->toBeFalse();
});
