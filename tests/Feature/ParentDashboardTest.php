<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Modules\Academics\Models\Attendance;
use Modules\Academics\Models\GroupSession;
use Modules\Academics\Models\TeachingGroup;
use Modules\Exams\Models\Exam;
use Modules\Exams\Models\ExamAttempt;
use Modules\Exams\Models\ExamResult;
use Modules\Notifications\Notifications\PortalNotification;
use Modules\People\Models\Student;

uses(RefreshDatabase::class);

it('shows a rich parent dashboard for only the authenticated parent children', function () {
    $this->travelTo(now()->setDate(2026, 5, 26)->setTime(10, 0));

    $parent = User::factory()->parent()->create();
    $otherParent = User::factory()->parent()->create();
    $student = Student::create([
        'parent_id' => $parent->id,
        'name' => 'Visible Child',
        'code' => 'ST-PC-10',
    ]);
    Student::create([
        'parent_id' => $otherParent->id,
        'name' => 'Hidden Child',
        'code' => 'ST-PC-99',
    ]);

    $group = TeachingGroup::create([
        'name' => 'Physics Group',
        'subject' => 'Physics',
    ]);
    $group->students()->sync([$student->id]);

    $session = GroupSession::create([
        'teaching_group_id' => $group->id,
        'title' => 'Tuesday Lesson',
        'starts_at' => now()->setDate(2026, 5, 26)->setTime(17, 0),
        'ends_at' => now()->setDate(2026, 5, 26)->setTime(18, 0),
    ]);
    Attendance::create([
        'teaching_session_id' => $session->id,
        'student_id' => $student->id,
        'status' => 'present',
        'notes' => 'Checked in from portal.',
    ]);

    $upcomingExam = Exam::query()->create([
        'teaching_group_id' => $group->id,
        'title' => 'Upcoming Quiz',
        'start_at' => now()->addDay(),
        'end_at' => now()->addDay()->addHour(),
        'max_allowed_time' => 30,
        'max_score' => 20,
        'student_review_mode' => 'score_only',
    ]);
    $conductedExam = Exam::query()->create([
        'teaching_group_id' => $group->id,
        'title' => 'Conducted Quiz',
        'start_at' => now()->subDays(2),
        'end_at' => now()->subDay(),
        'max_allowed_time' => 30,
        'max_score' => 20,
        'student_review_mode' => 'score_only',
    ]);
    ExamAttempt::query()->create([
        'exam_id' => $conductedExam->id,
        'student_id' => $student->id,
        'started_at' => now()->subDays(2),
        'submitted_at' => now()->subDays(2)->addMinutes(20),
        'expires_at' => now()->subDays(2)->addMinutes(30),
        'status' => 'submitted',
    ]);
    ExamResult::query()->create([
        'exam_id' => $conductedExam->id,
        'student_id' => $student->id,
        'score' => 18,
    ]);

    $this->travelTo(now()->addMinute());

    $student->user->notify(new PortalNotification([
        'audience' => 'student',
        'body' => 'Bring your worksheet.',
        'parent_id' => $parent->id,
        'reference_key' => 'test:student-dashboard-note',
        'student_code' => $student->code,
        'student_id' => $student->id,
        'student_name' => $student->name,
        'title' => 'Worksheet reminder',
        'type' => 'general',
    ]));

    $this->actingAs($parent)
        ->get(route('parent.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Parent/Dashboard')
            ->has('children', 1)
            ->where('children.0.name', 'Visible Child')
            ->where('children.0.groups.0.name', 'Physics Group')
            ->where('children.0.week.starts_at', 'May 23, 2026')
            ->where('children.0.week.days.3.sessions.0.title', 'Tuesday Lesson')
            ->where('children.0.week.days.3.sessions.0.attendance_status', 'present')
            ->where('children.0.upcoming_sessions.0.title', 'Tuesday Lesson')
            ->where('children.0.recent_attendance.0.status', 'present')
            ->where('children.0.upcoming_exams.0.title', 'Upcoming Quiz')
            ->where('children.0.conducted_exams.0.title', 'Conducted Quiz')
            ->missing('children.0.conducted_exams.0.attempt_url')
            ->missing('children.0.conducted_exams.0.show_url')
            ->where('children.0.latest_exam_title', 'Conducted Quiz')
            ->where('children.0.recent_notifications.0.title', 'Worksheet reminder'));
});
