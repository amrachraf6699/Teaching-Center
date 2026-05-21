<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Academics\Models\Attendance;
use Modules\Academics\Models\GroupSession;
use Modules\Academics\Models\TeachingGroup;
use Modules\Exams\Models\Exam;
use Modules\Exams\Models\ExamResult;
use Modules\Notifications\Models\ParentNotification;
use Modules\People\Models\Student;

uses(RefreshDatabase::class);

it('redirects guests to the login page', function () {
    $this->get('/')->assertRedirect(route('login'));
});

it('redirects unauthenticated admin requests to the login page', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

it('logs teachers and parents in from the same login page', function () {
    $teacher = User::factory()->teacher()->create([
        'email' => 'teacher@example.test',
        'password' => bcrypt('password'),
    ]);
    $parent = User::factory()->parent()->create([
        'email' => 'parent@example.test',
        'password' => bcrypt('password'),
    ]);

    $this->post(route('login.store'), [
        'email' => $teacher->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard'));

    auth()->logout();
    session()->invalidate();
    session()->regenerateToken();

    $this->post(route('login.store'), [
        'email' => $parent->email,
        'password' => 'password',
    ])->assertRedirect(route('parent.dashboard'));
});

it('separates teacher admin access from parent portal access', function () {
    $teacher = User::factory()->teacher()->create();
    $parent = User::factory()->parent()->create();

    $this->actingAs($teacher)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Teacher Dashboard');

    $this->actingAs($teacher)
        ->get(route('parent.dashboard'))
        ->assertForbidden();

    $this->actingAs($parent)
        ->get(route('admin.dashboard'))
        ->assertForbidden();

    $this->actingAs($parent)
        ->get(route('parent.dashboard'))
        ->assertOk()
        ->assertSee('Parent Portal');
});

it('only shows a parent their own children in the portal', function () {
    $parent = User::factory()->parent()->create();
    $otherParent = User::factory()->parent()->create();

    Student::create(['parent_id' => $parent->id, 'name' => 'Visible Student']);
    Student::create(['parent_id' => $otherParent->id, 'name' => 'Hidden Student']);

    $this->actingAs($parent)
        ->get(route('parent.dashboard'))
        ->assertOk()
        ->assertSee('Visible Student')
        ->assertDontSee('Hidden Student');
});

it('creates students, groups, sessions, attendance, exams, grades, and notifications', function () {
    $teacher = User::factory()->teacher()->create();
    $parent = User::factory()->parent()->create();

    $this->actingAs($teacher)
        ->post(route('admin.students.store'), [
            'parent_id' => $parent->id,
            'name' => 'Student One',
            'code' => 'ST-001',
        ])
        ->assertRedirect(route('admin.students.index'));

    $student = Student::query()->where('code', 'ST-001')->firstOrFail();

    $this->actingAs($teacher)
        ->post(route('admin.groups.store'), [
            'name' => 'Math A',
            'subject' => 'Math',
            'student_ids' => [$student->id],
        ])
        ->assertRedirect(route('admin.groups.index'));

    $group = TeachingGroup::query()->where('name', 'Math A')->firstOrFail();
    expect($group->students()->whereKey($student->id)->exists())->toBeTrue();

    $this->actingAs($teacher)
        ->post(route('admin.sessions.store'), [
            'teaching_group_id' => $group->id,
            'title' => 'Session 1',
            'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
        ])
        ->assertRedirect(route('admin.sessions.index'));

    $session = GroupSession::query()->where('title', 'Session 1')->firstOrFail();

    $this->actingAs($teacher)
        ->post(route('admin.attendance.store'), [
            'teaching_session_id' => $session->id,
            'student_id' => $student->id,
            'status' => 'present',
        ])
        ->assertRedirect();

    expect(Attendance::query()->where('student_id', $student->id)->where('status', 'present')->exists())->toBeTrue();

    $this->actingAs($teacher)
        ->post(route('admin.exams.store'), [
            'teaching_group_id' => $group->id,
            'title' => 'Midterm',
            'exam_date' => now()->toDateString(),
            'max_score' => 100,
        ])
        ->assertRedirect(route('admin.exams.index'));

    $exam = Exam::query()->where('title', 'Midterm')->firstOrFail();

    $this->actingAs($teacher)
        ->post(route('admin.exam-results.store', $exam), [
            'student_id' => $student->id,
            'score' => 92,
        ])
        ->assertRedirect();

    expect(ExamResult::query()->where('exam_id', $exam->id)->where('score', 92)->exists())->toBeTrue();
    expect(ParentNotification::query()->where('parent_id', $parent->id)->count())->toBe(2);
});
