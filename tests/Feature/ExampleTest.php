<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
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

it('renders the unified login inertia page', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Auth/Login')
            ->where('title', 'Login')
            ->where('action', route('login.store')));
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
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard')
            ->has('metrics', 5));

    $this->actingAs($teacher)
        ->get(route('parent.dashboard'))
        ->assertForbidden();

    $this->actingAs($parent)
        ->get(route('admin.dashboard'))
        ->assertForbidden();

    $this->actingAs($parent)
        ->get(route('parent.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Parent/Dashboard')
            ->has('children')
            ->has('notifications'));
});

it('only shows a parent their own children in the portal', function () {
    $parent = User::factory()->parent()->create();
    $otherParent = User::factory()->parent()->create();

    Student::create(['parent_id' => $parent->id, 'name' => 'Visible Student']);
    Student::create(['parent_id' => $otherParent->id, 'name' => 'Hidden Student']);

    $this->actingAs($parent)
        ->get(route('parent.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Parent/Dashboard')
            ->has('children', 1)
            ->where('children.0.name', 'Visible Student'));
});

it('auto-generates sequential student codes when no code is provided', function () {
    $teacher = User::factory()->teacher()->create();
    $parent = User::factory()->parent()->create();

    Student::create([
        'parent_id' => $parent->id,
        'name' => 'Existing Student',
        'code' => 'ST-009',
    ]);

    $this->actingAs($teacher)
        ->post(route('admin.students.store'), [
            'parent_id' => $parent->id,
            'name' => 'Auto Coded Student',
        ])
        ->assertRedirect(route('admin.students.index'));

    expect(Student::query()->where('name', 'Auto Coded Student')->firstOrFail()->code)->toBe('ST-010');
});

it('supports full admin crud routes with relationship-rich show pages', function () {
    $teacher = User::factory()->teacher()->create();
    $parent = User::factory()->parent()->create(['name' => 'Original Parent']);
    $student = Student::create(['parent_id' => $parent->id, 'name' => 'Original Student', 'code' => 'ST-101']);
    $group = TeachingGroup::create(['name' => 'Original Group', 'subject' => 'Math']);
    $group->students()->sync([$student->id]);
    $session = GroupSession::create([
        'teaching_group_id' => $group->id,
        'title' => 'Original Session',
        'starts_at' => now()->addDay(),
    ]);
    $exam = Exam::create([
        'teaching_group_id' => $group->id,
        'title' => 'Original Exam',
        'exam_date' => now()->toDateString(),
        'max_score' => 100,
    ]);
    Attendance::create([
        'teaching_session_id' => $session->id,
        'student_id' => $student->id,
        'status' => 'present',
    ]);
    ExamResult::create([
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'score' => 95,
    ]);
    ParentNotification::create([
        'parent_id' => $parent->id,
        'student_id' => $student->id,
        'type' => 'general',
        'title' => 'Update',
        'body' => 'Relationship data.',
    ]);

    $this->actingAs($teacher)
        ->get(route('admin.parents.show', $parent))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Parents/Show')
            ->has('parent.children', 1)
            ->has('parent.notifications', 1));

    $this->actingAs($teacher)
        ->get(route('admin.students.show', $student))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Students/Show')
            ->has('student.groups', 1)
            ->has('student.attendance', 1)
            ->has('student.exam_results', 1)
            ->has('student.notifications', 1));

    $this->actingAs($teacher)
        ->get(route('admin.groups.show', $group))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Groups/Show')
            ->has('group.students', 1)
            ->has('group.sessions', 1)
            ->has('group.exams', 1));

    $this->actingAs($teacher)
        ->get(route('admin.sessions.show', $session))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Sessions/Show')
            ->has('session.students', 1)
            ->has('session.attendance', 1));

    $this->actingAs($teacher)
        ->get(route('admin.exams.show', $exam))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Exams/Show')
            ->has('exam.students', 1)
            ->has('exam.results', 1));

    $this->actingAs($teacher)
        ->put(route('admin.parents.update', $parent), [
            'name' => 'Updated Parent',
            'email' => $parent->email,
            'password' => '',
        ])
        ->assertRedirect(route('admin.parents.show', $parent));
    expect($parent->refresh()->name)->toBe('Updated Parent');

    $this->actingAs($teacher)
        ->put(route('admin.students.update', $student), [
            'parent_id' => $parent->id,
            'name' => 'Updated Student',
            'code' => 'ST-102',
            'phone' => '01011111111',
            'date_of_birth' => null,
            'notes' => 'Updated notes.',
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.students.show', $student));
    expect($student->refresh()->name)->toBe('Updated Student');

    $this->actingAs($teacher)
        ->put(route('admin.groups.update', $group), [
            'name' => 'Updated Group',
            'subject' => 'Science',
            'level' => null,
            'description' => 'Updated group.',
            'is_active' => true,
            'student_ids' => [$student->id],
        ])
        ->assertRedirect(route('admin.groups.show', $group));
    expect($group->refresh()->name)->toBe('Updated Group');

    $this->actingAs($teacher)
        ->put(route('admin.sessions.update', $session), [
            'teaching_group_id' => $group->id,
            'title' => 'Updated Session',
            'starts_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'ends_at' => null,
            'notes' => 'Updated session.',
        ])
        ->assertRedirect(route('admin.sessions.show', $session));
    expect($session->refresh()->title)->toBe('Updated Session');

    $this->actingAs($teacher)
        ->put(route('admin.exams.update', $exam), [
            'teaching_group_id' => $group->id,
            'title' => 'Updated Exam',
            'exam_date' => now()->addWeek()->toDateString(),
            'max_score' => 120,
            'notes' => 'Updated exam.',
        ])
        ->assertRedirect(route('admin.exams.show', $exam));
    expect($exam->refresh()->title)->toBe('Updated Exam');

    $this->actingAs($teacher)->delete(route('admin.exams.destroy', $exam))->assertRedirect(route('admin.exams.index'));
    $this->actingAs($teacher)->delete(route('admin.sessions.destroy', $session))->assertRedirect(route('admin.sessions.index'));
    $this->actingAs($teacher)->delete(route('admin.groups.destroy', $group))->assertRedirect(route('admin.groups.index'));
    $this->actingAs($teacher)->delete(route('admin.students.destroy', $student))->assertRedirect(route('admin.students.index'));
    $this->actingAs($teacher)->delete(route('admin.parents.destroy', $parent))->assertRedirect(route('admin.parents.index'));

    expect(Exam::query()->whereKey($exam->id)->exists())->toBeFalse();
    expect(GroupSession::query()->whereKey($session->id)->exists())->toBeFalse();
    expect(TeachingGroup::query()->whereKey($group->id)->exists())->toBeFalse();
    expect(Student::query()->whereKey($student->id)->exists())->toBeFalse();
    expect(User::query()->whereKey($parent->id)->exists())->toBeFalse();
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
