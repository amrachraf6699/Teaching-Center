<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Modules\Academics\Models\TeachingGroup;
use Modules\Exams\Models\Exam;
use Modules\Exams\Models\ExamAttempt;
use Modules\Exams\Models\ExamQuestion;
use Modules\Exams\Models\ExamQuestionOption;
use Modules\Exams\Models\ExamResult;
use Modules\People\Models\Student;

uses(RefreshDatabase::class);

function createStudentExam(TeachingGroup $group, array $overrides = []): Exam
{
    $exam = Exam::query()->create(array_merge([
        'teaching_group_id' => $group->id,
        'title' => 'Physics Quiz',
        'start_at' => now()->subMinutes(5),
        'end_at' => now()->addMinutes(55),
        'max_allowed_time' => 20,
        'max_score' => 10,
        'student_review_mode' => 'score_only',
    ], $overrides));

    $question = ExamQuestion::query()->create([
        'exam_id' => $exam->id,
        'type' => 'mcq',
        'prompt' => 'Correct option?',
        'points' => 10,
        'position' => 1,
    ]);

    ExamQuestionOption::query()->create([
        'exam_question_id' => $question->id,
        'label' => 'A',
        'is_correct' => true,
        'position' => 1,
    ]);
    ExamQuestionOption::query()->create([
        'exam_question_id' => $question->id,
        'label' => 'B',
        'is_correct' => false,
        'position' => 2,
    ]);

    return $exam->fresh(['questions.options']);
}

it('lists upcoming and conducted exams for the student and supports a timed attempt flow', function () {
    $parent = User::factory()->parent()->create();
    $student = Student::create([
        'parent_id' => $parent->id,
        'name' => 'Exam Student',
    ]);

    $group = TeachingGroup::create([
        'name' => 'Physics Group',
        'subject' => 'Physics',
    ]);
    $otherGroup = TeachingGroup::create([
        'name' => 'Math Group',
        'subject' => 'Math',
    ]);
    $group->students()->sync([$student->id]);

    $availableExam = createStudentExam($group, ['title' => 'Available Exam']);
    $closedExam = createStudentExam($group, [
        'title' => 'Closed Exam',
        'start_at' => now()->subDays(2),
        'end_at' => now()->subDay(),
    ]);
    createStudentExam($otherGroup, ['title' => 'Other Group Exam']);

    $this->actingAs($student->user)
        ->get(route('student.exams.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Student/Exams/Index')
            ->has('upcomingExams', 1)
            ->where('upcomingExams.0.title', 'Available Exam')
            ->has('conductedExams', 1)
            ->where('conductedExams.0.title', 'Closed Exam'));

    $this->actingAs($student->user)
        ->post(route('student.exams.start', $availableExam))
        ->assertRedirect();

    $attempt = ExamAttempt::query()->where('exam_id', $availableExam->id)->where('student_id', $student->id)->firstOrFail();
    $correctOption = $availableExam->questions->first()->options->firstWhere('is_correct', true);

    $this->actingAs($student->user)
        ->post(route('student.exams.attempt.answer', [$availableExam, $attempt]), [
            'exam_question_id' => $availableExam->questions->first()->id,
            'selected_option_id' => $correctOption->id,
        ])
        ->assertRedirect();

    $this->actingAs($student->user)
        ->post(route('student.exams.attempt.submit', [$availableExam, $attempt]))
        ->assertRedirect(route('student.exams.attempt.show', [$availableExam, $attempt]));

    expect(ExamResult::query()->where('exam_id', $availableExam->id)->where('student_id', $student->id)->firstOrFail()->score)
        ->toBe('10.00');

    $this->actingAs($student->user)
        ->get(route('student.exams.attempt.show', [$availableExam, $attempt]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Student/Exams/Attempt')
            ->where('exam.review_mode', 'score_only')
            ->where('attempt.questions.0.options.0.is_correct', null));
});

it('exposes detailed review only when the teacher enables question review', function () {
    $parent = User::factory()->parent()->create();
    $student = Student::create([
        'parent_id' => $parent->id,
        'name' => 'Review Student',
    ]);

    $group = TeachingGroup::create([
        'name' => 'Chemistry Group',
        'subject' => 'Chemistry',
    ]);
    $group->students()->sync([$student->id]);

    $exam = createStudentExam($group, ['student_review_mode' => 'question_review']);
    $attempt = ExamAttempt::query()->create([
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'started_at' => now()->subMinutes(10),
        'submitted_at' => now()->subMinutes(1),
        'expires_at' => now()->subMinutes(1),
        'status' => 'submitted',
    ]);
    $correctOption = $exam->questions->first()->options->firstWhere('is_correct', true);

    \Modules\Exams\Models\ExamAttemptAnswer::query()->create([
        'exam_attempt_id' => $attempt->id,
        'exam_question_id' => $exam->questions->first()->id,
        'selected_option_id' => $correctOption->id,
        'is_correct' => true,
        'earned_points' => 10,
    ]);
    ExamResult::query()->create([
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'score' => 10,
    ]);

    $this->actingAs($student->user)
        ->get(route('student.exams.attempt.show', [$exam, $attempt]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('attempt.questions.0.options.0.is_correct', true)
            ->where('attempt.questions.0.earned_points', '10.00'));
});
