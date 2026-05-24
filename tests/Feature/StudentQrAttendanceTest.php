<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Modules\Academics\Models\Attendance;
use Modules\Academics\Models\GroupSession;
use Modules\Academics\Models\TeachingGroup;
use Modules\Notifications\Models\ParentNotification;
use Modules\People\Models\Student;

uses(RefreshDatabase::class);

it('allows a student to log in with code and mark attendance from a signed qr link', function () {
    $teacher = User::factory()->teacher()->create();
    $parent = User::factory()->parent()->create();
    $student = Student::create([
        'parent_id' => $parent->id,
        'name' => 'Scan Student',
        'code' => 'ST-901',
    ]);

    $student->user->forceFill([
        'password' => Hash::make('secret123'),
    ])->save();

    $group = TeachingGroup::create([
        'name' => 'Chemistry Group',
        'subject' => 'Chemistry',
    ]);
    $group->students()->sync([$student->id]);

    $session = GroupSession::create([
        'teaching_group_id' => $group->id,
        'title' => 'Chemistry Session',
        'starts_at' => now()->addDay()->setTime(17, 0),
        'ends_at' => now()->addDay()->setTime(18, 30),
    ]);

    $scanUrl = URL::signedRoute('student.sessions.scan', ['session' => $session]);
    $submitUrl = URL::signedRoute('student.sessions.attendance.store', ['session' => $session]);

    $this->get($scanUrl)
        ->assertRedirect(route('student.login', ['redirect' => $scanUrl]));

    $this->post(route('student.login.store'), [
        'code' => 'ST-901',
        'password' => 'secret123',
        'redirect' => $scanUrl,
    ])->assertRedirect($scanUrl);

    $this->actingAs($student->user)
        ->get($scanUrl)
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Student/AttendanceScan'));

    $this->actingAs($student->user)
        ->post($submitUrl)
        ->assertRedirect();

    expect(Attendance::query()->where('teaching_session_id', $session->id)->where('student_id', $student->id)->firstOrFail()->status)->toBe('present')
        ->and(ParentNotification::count())->toBe(0);

    $this->actingAs($student->user)
        ->post($submitUrl)
        ->assertRedirect();

    expect(Attendance::query()->where('teaching_session_id', $session->id)->where('student_id', $student->id)->count())->toBe(1);

    $this->actingAs($teacher)
        ->post(route('admin.attendance.store'), [
            'teaching_session_id' => $session->id,
            'student_id' => $student->id,
            'status' => 'late',
            'notes' => 'Teacher corrected after review.',
        ])
        ->assertRedirect();

    expect(Attendance::query()->where('teaching_session_id', $session->id)->where('student_id', $student->id)->firstOrFail()->status)->toBe('late')
        ->and(ParentNotification::count())->toBe(1);
});
