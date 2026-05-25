<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Notifications\Models\ParentNotification;
use Modules\People\Models\Student;

uses(RefreshDatabase::class);

it('lets a teacher send a notification to a parent account from admin', function () {
    $teacher = User::factory()->teacher()->create();
    $parent = User::factory()->parent()->create();
    $student = Student::create([
        'parent_id' => $parent->id,
        'name' => 'Parent Target Student',
    ]);

    $this->actingAs($teacher)
        ->get(route('admin.notifications.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Notifications/Index')
            ->has('students', 1));

    $this->actingAs($teacher)
        ->post(route('admin.notifications.store'), [
            'student_id' => $student->id,
            'recipient' => 'parent',
            'type' => 'general',
            'title' => 'Parent alert',
            'body' => 'Please review the latest update.',
        ])
        ->assertRedirect(route('admin.notifications.index', [
            'student_id' => $student->id,
            'recipient' => 'parent',
        ]));

    expect(ParentNotification::query()->count())->toBe(1);

    $notification = ParentNotification::query()->firstOrFail();

    expect($notification->recipient_role)->toBe('parent')
        ->and($notification->recipient_user_id)->toBe($parent->id)
        ->and($notification->parent_id)->toBe($parent->id);

    $this->actingAs($parent)
        ->get(route('parent.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Parent/Dashboard')
            ->has('notifications', 1)
            ->where('notifications.0.title', 'Parent alert'));
});

it('lets a teacher send a notification to a student account without exposing it to the parent feed', function () {
    $teacher = User::factory()->teacher()->create();
    $parent = User::factory()->parent()->create();
    $student = Student::create([
        'parent_id' => $parent->id,
        'name' => 'Student Target',
    ]);

    $this->actingAs($teacher)
        ->post(route('admin.notifications.store'), [
            'student_id' => $student->id,
            'recipient' => 'student',
            'type' => 'reminder',
            'title' => 'Bring your notebook',
            'body' => 'The next class needs your solved homework.',
        ])
        ->assertRedirect(route('admin.notifications.index', [
            'student_id' => $student->id,
            'recipient' => 'student',
        ]));

    $notification = ParentNotification::query()->firstOrFail();

    expect($notification->recipient_role)->toBe('student')
        ->and($notification->recipient_user_id)->toBe($student->user_id)
        ->and($notification->parent_id)->toBe($parent->id);

    $this->actingAs($student->user)
        ->get(route('student.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Student/Dashboard')
            ->has('student.notifications', 1)
            ->where('student.notifications.0.title', 'Bring your notebook'));

    $this->actingAs($parent)
        ->get(route('parent.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Parent/Dashboard')
            ->has('notifications', 0));
});
