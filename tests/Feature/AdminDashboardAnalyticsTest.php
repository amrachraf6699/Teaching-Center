<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Modules\Academics\Models\Attendance;
use Modules\Academics\Models\GroupSession;
use Modules\Academics\Models\TeachingGroup;
use Modules\Exams\Models\Exam;
use Modules\Exams\Models\ExamAttempt;
use Modules\People\Models\Student;

uses(RefreshDatabase::class);

it('renders teacher dashboard analytics metrics and chart payloads', function () {
    Carbon::setTestNow('2026-05-29 12:00:00');

    $teacher = User::factory()->teacher()->create();
    $parent = User::factory()->parent()->create();
    $groups = collect(range(1, 6))->map(fn (int $index) => TeachingGroup::create([
        'name' => "Group {$index}",
        'subject' => 'Math',
        'is_active' => $index !== 6,
    ]));
    $students = collect(range(1, 6))->map(function (int $index) use ($parent): Student {
        $student = Student::create([
            'parent_id' => $parent->id,
            'name' => "Student {$index}",
            'code' => "ST-90{$index}",
            'is_active' => $index !== 6,
        ]);
        $student->forceFill([
            'created_at' => now()->startOfMonth()->subMonths(6 - $index)->addDay(),
            'updated_at' => now()->startOfMonth()->subMonths(6 - $index)->addDay(),
        ])->save();

        return $student;
    });

    $groups->each(function (TeachingGroup $group, int $index) use ($students): void {
        $group->students()->sync($students->take(6 - $index)->pluck('id')->all());
    });

    $weekSessionOne = GroupSession::create([
        'teaching_group_id' => $groups[0]->id,
        'title' => 'Saturday Session',
        'starts_at' => '2026-05-23 10:00:00',
        'ends_at' => '2026-05-23 11:00:00',
    ]);
    GroupSession::create([
        'teaching_group_id' => $groups[0]->id,
        'title' => 'Friday Session',
        'starts_at' => '2026-05-29 10:00:00',
        'ends_at' => '2026-05-29 11:00:00',
    ]);
    $attendanceSession = GroupSession::create([
        'teaching_group_id' => $groups[0]->id,
        'title' => 'Attendance Session',
        'starts_at' => '2026-05-28 10:00:00',
        'ends_at' => '2026-05-28 11:00:00',
    ]);

    Attendance::create(['teaching_session_id' => $attendanceSession->id, 'student_id' => $students[0]->id, 'status' => 'present']);
    Attendance::create(['teaching_session_id' => $attendanceSession->id, 'student_id' => $students[1]->id, 'status' => 'absent']);
    Attendance::create(['teaching_session_id' => $attendanceSession->id, 'student_id' => $students[2]->id, 'status' => 'late']);

    $upcomingExam = Exam::create([
        'teaching_group_id' => $groups[0]->id,
        'title' => 'Upcoming Exam',
        'start_at' => now()->addDay(),
        'end_at' => now()->addDay()->addHour(),
        'max_allowed_time' => 60,
        'max_score' => 100,
    ]);
    Exam::create([
        'teaching_group_id' => $groups[0]->id,
        'title' => 'Conducted Exam',
        'start_at' => now()->subDays(2),
        'end_at' => now()->subDays(2)->addHour(),
        'max_allowed_time' => 60,
        'max_score' => 100,
    ]);
    ExamAttempt::create([
        'exam_id' => $upcomingExam->id,
        'student_id' => $students[0]->id,
        'started_at' => now(),
        'submitted_at' => null,
        'expires_at' => now()->addHour(),
        'status' => 'in_progress',
    ]);
    ExamAttempt::create([
        'exam_id' => $upcomingExam->id,
        'student_id' => $students[1]->id,
        'started_at' => now(),
        'submitted_at' => now()->addMinutes(20),
        'expires_at' => now()->addHour(),
        'status' => 'submitted',
    ]);

    $this->actingAs($teacher)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard')
            ->has('metrics', 6)
            ->where('metrics.0.label_key', 'teacherDashboard.metric.totalStudents')
            ->where('metrics.0.value', 6)
            ->where('metrics.1.label_key', 'teacherDashboard.metric.activeStudents')
            ->where('metrics.1.value', 5)
            ->where('metrics.4.label_key', 'teacherDashboard.metric.sessionsThisWeek')
            ->where('metrics.4.value', 3)
            ->where('metrics.5.label_key', 'teacherDashboard.metric.upcomingExams')
            ->where('metrics.5.value', 1)
            ->has('charts.attendanceTrend.labels', 14)
            ->where('charts.attendanceTrend.datasets.0.label_key', 'teacherDashboard.chart.present')
            ->where('charts.attendanceTrend.datasets.0.data.12', 1)
            ->where('charts.attendanceTrend.datasets.1.data.12', 1)
            ->where('charts.attendanceTrend.datasets.2.data.12', 1)
            ->where('charts.weeklySessions.labels', ['Sat May 23', 'Sun May 24', 'Mon May 25', 'Tue May 26', 'Wed May 27', 'Thu May 28', 'Fri May 29'])
            ->where('charts.weeklySessions.datasets.0.label_key', 'teacherDashboard.chart.sessions')
            ->where('charts.weeklySessions.datasets.0.data', [1, 0, 0, 0, 0, 1, 1])
            ->where('charts.examPipeline.label_keys', [
                'teacherDashboard.chart.upcomingExams',
                'teacherDashboard.chart.conductedExams',
                'teacherDashboard.chart.inProgressAttempts',
                'teacherDashboard.chart.submittedAttempts',
            ])
            ->where('charts.examPipeline.datasets.0.label_key', 'teacherDashboard.chart.examPipeline')
            ->where('charts.examPipeline.datasets.0.data', [1, 1, 1, 1])
            ->has('charts.studentGrowth.labels', 6)
            ->where('charts.studentGrowth.datasets.0.label_key', 'teacherDashboard.chart.newStudents')
            ->where('charts.studentGrowth.datasets.0.data', [1, 1, 1, 1, 1, 1])
            ->where('charts.groupLoad.labels', ['Group 1', 'Group 2', 'Group 3', 'Group 4', 'Group 5'])
            ->where('charts.groupLoad.datasets.0.label_key', 'teacherDashboard.chart.students')
            ->where('charts.groupLoad.datasets.0.data', [6, 5, 4, 3, 2]));

    Carbon::setTestNow();
});
