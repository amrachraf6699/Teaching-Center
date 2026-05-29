<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Queue;
use Modules\Academics\Models\TeachingGroup;
use Modules\Exams\Actions\DispatchScheduledExamReminders;
use Modules\Exams\Jobs\NotifyStudentsOfExamReminder;
use Modules\Exams\Jobs\NotifyStudentsOfNewExam;
use Modules\Exams\Models\Exam;
use Modules\Notifications\Notifications\PortalNotification;
use Modules\Notifications\Services\PortalNotificationService;
use Modules\People\Models\Student;

uses(RefreshDatabase::class);

function notificationExamPayload(TeachingGroup $group, array $overrides = []): array
{
    return array_replace_recursive([
        'teaching_group_id' => $group->id,
        'title' => 'Physics Final',
        'start_at' => now()->addDay()->format('Y-m-d H:i:s'),
        'end_at' => now()->addDay()->addHours(2)->format('Y-m-d H:i:s'),
        'max_allowed_time' => 90,
        'questions' => [
            [
                'type' => 'true_false',
                'prompt' => 'Force equals mass times acceleration.',
                'points' => 20,
                'correct_boolean' => 'true',
                'options' => [],
            ],
            [
                'type' => 'mcq',
                'prompt' => 'Which unit measures electric current?',
                'points' => 30,
                'options' => [
                    ['label' => 'Ampere', 'is_correct' => true],
                    ['label' => 'Joule', 'is_correct' => false],
                ],
            ],
        ],
    ], $overrides);
}

it('dispatches a queued notification job when a new exam is created', function () {
    Queue::fake();

    $teacher = User::factory()->teacher()->create();
    $group = TeachingGroup::create(['name' => 'Physics Group']);

    $this->actingAs($teacher)
        ->post(route('admin.exams.store'), notificationExamPayload($group))
        ->assertRedirect(route('admin.exams.index'));

    Queue::assertPushed(NotifyStudentsOfNewExam::class);
});

it('creates a student notification for each student when the new exam job runs', function () {
    $parent = User::factory()->parent()->create();
    $student = Student::create([
        'parent_id' => $parent->id,
        'name' => 'Queued Student',
    ]);
    $group = TeachingGroup::create(['name' => 'Physics Group']);
    $group->students()->sync([$student->id]);

    $exam = Exam::create([
        'teaching_group_id' => $group->id,
        'title' => 'Physics Final',
        'start_at' => now()->addDays(2),
        'end_at' => now()->addDays(2)->addHour(),
        'max_allowed_time' => 60,
        'max_score' => 100,
        'student_review_mode' => 'score_only',
    ]);

    (new NotifyStudentsOfNewExam($exam->id))->handle(app(PortalNotificationService::class));

    expect(DatabaseNotification::query()
        ->where('type', PortalNotification::class)
        ->where('notifiable_id', $student->user_id)
        ->where('notifiable_type', User::class)
        ->where('data->student_id', $student->id)
        ->where('data->audience', 'student')
        ->where('data->type', 'exam_created')
        ->count())->toBe(1);
});

it('dispatches day and hour reminder jobs for exams in the matching reminder windows', function () {
    Queue::fake();

    $group = TeachingGroup::create(['name' => 'Physics Group']);

    Exam::create([
        'teaching_group_id' => $group->id,
        'title' => 'Tomorrow Exam',
        'start_at' => now()->addDay()->startOfMinute()->addSeconds(10),
        'end_at' => now()->addDay()->addHour(),
        'max_allowed_time' => 60,
        'max_score' => 100,
        'student_review_mode' => 'score_only',
    ]);

    Exam::create([
        'teaching_group_id' => $group->id,
        'title' => 'Soon Exam',
        'start_at' => now()->addHour()->startOfMinute()->addSeconds(20),
        'end_at' => now()->addHour()->addHour(),
        'max_allowed_time' => 60,
        'max_score' => 100,
        'student_review_mode' => 'score_only',
    ]);

    app(DispatchScheduledExamReminders::class)->handle();

    Queue::assertPushed(NotifyStudentsOfExamReminder::class, fn (NotifyStudentsOfExamReminder $job) => $job->window === 'day');
    Queue::assertPushed(NotifyStudentsOfExamReminder::class, fn (NotifyStudentsOfExamReminder $job) => $job->window === 'hour');
});

it('deduplicates reminder notifications when the reminder job runs more than once', function () {
    $parent = User::factory()->parent()->create();
    $student = Student::create([
        'parent_id' => $parent->id,
        'name' => 'Reminder Student',
    ]);
    $group = TeachingGroup::create(['name' => 'Chemistry Group']);
    $group->students()->sync([$student->id]);

    $exam = Exam::create([
        'teaching_group_id' => $group->id,
        'title' => 'Chemistry Final',
        'start_at' => now()->addHour(),
        'end_at' => now()->addHours(2),
        'max_allowed_time' => 60,
        'max_score' => 100,
        'student_review_mode' => 'score_only',
    ]);

    $job = new NotifyStudentsOfExamReminder($exam->id, 'hour');

    $job->handle(app(PortalNotificationService::class));
    $job->handle(app(PortalNotificationService::class));

    expect(DatabaseNotification::query()
        ->where('type', PortalNotification::class)
        ->where('notifiable_id', $student->user_id)
        ->where('notifiable_type', User::class)
        ->where('data->student_id', $student->id)
        ->where('data->audience', 'student')
        ->where('data->reference_key', sprintf('exam:%d:student:%d:reminder:hour', $exam->id, $student->id))
        ->count())->toBe(1);
});
