<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Modules\Academics\Models\GroupSession;
use Modules\Academics\Models\TeachingGroup;
use Modules\People\Models\Student;

uses(RefreshDatabase::class);

it('shows the student weekly timetable from saturday to friday and exposes the scanner page', function () {
    $this->travelTo(now()->setDate(2026, 5, 26)->setTime(10, 0));

    $parent = User::factory()->parent()->create();
    $student = Student::create([
        'parent_id' => $parent->id,
        'name' => 'Weekly Student',
    ]);
    $group = TeachingGroup::create([
        'name' => 'Physics Group',
        'subject' => 'Physics',
    ]);
    $group->students()->sync([$student->id]);

    GroupSession::create([
        'teaching_group_id' => $group->id,
        'title' => 'Saturday Session',
        'starts_at' => now()->setDate(2026, 5, 23)->setTime(14, 0),
        'ends_at' => now()->setDate(2026, 5, 23)->setTime(15, 0),
    ]);
    GroupSession::create([
        'teaching_group_id' => $group->id,
        'title' => 'Tuesday Session',
        'starts_at' => now()->setDate(2026, 5, 26)->setTime(17, 0),
        'ends_at' => now()->setDate(2026, 5, 26)->setTime(18, 0),
    ]);

    $this->actingAs($student->user)
        ->get(route('student.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Student/Dashboard')
            ->where('student.week.starts_at', 'May 23, 2026')
            ->where('student.week.ends_at', 'May 29, 2026')
            ->has('student.week.days', 7)
            ->where('student.week.days.0.day_name', 'Saturday')
            ->where('student.week.days.0.sessions.0.title', 'Saturday Session')
            ->where('student.week.days.3.day_name', 'Tuesday')
            ->where('student.week.days.3.sessions.0.title', 'Tuesday Session'));

    $this->actingAs($student->user)
        ->get(route('student.scan-attendance'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Student/ScanAttendance'));
});
