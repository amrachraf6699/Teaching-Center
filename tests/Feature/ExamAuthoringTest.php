<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Modules\Academics\Models\TeachingGroup;
use Modules\Exams\Models\Exam;

uses(RefreshDatabase::class);

function examPayload(TeachingGroup $group, array $overrides = []): array
{
    return array_replace_recursive([
        'teaching_group_id' => $group->id,
        'title' => 'Physics Final',
        'start_at' => now()->format('Y-m-d H:i:s'),
        'end_at' => now()->addHours(2)->format('Y-m-d H:i:s'),
        'max_allowed_time' => 90,
        'notes' => 'Comprehensive revision exam.',
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
                    ['label' => 'Pascal', 'is_correct' => false],
                ],
            ],
        ],
    ], $overrides);
}

it('creates an exam with computed max score and authored questions', function () {
    $teacher = User::factory()->teacher()->create();
    $group = TeachingGroup::create(['name' => 'Physics Group']);

    $this->actingAs($teacher)
        ->post(route('admin.exams.store'), examPayload($group))
        ->assertRedirect(route('admin.exams.index'));

    $exam = Exam::query()->where('title', 'Physics Final')->firstOrFail();

    expect((float) $exam->max_score)->toBe(50.0)
        ->and($exam->questions()->count())->toBe(2)
        ->and($exam->questions()->where('type', 'mcq')->firstOrFail()->options()->count())->toBe(3);
});

it('updates an exam and preserves question ids while recomputing max score', function () {
    $teacher = User::factory()->teacher()->create();
    $group = TeachingGroup::create(['name' => 'Physics Group']);

    $this->actingAs($teacher)->post(route('admin.exams.store'), examPayload($group));

    $exam = Exam::query()->with('questions.options')->firstOrFail();
    $firstQuestion = $exam->questions[0];
    $secondQuestion = $exam->questions[1];

    $this->actingAs($teacher)
        ->put(route('admin.exams.update', $exam), examPayload($group, [
            'title' => 'Physics Final Updated',
            'questions' => [
                [
                    'id' => $firstQuestion->id,
                    'type' => 'true_false',
                    'prompt' => 'Updated true or false question',
                    'points' => 10,
                    'correct_boolean' => 'false',
                    'options' => $firstQuestion->options->map(fn ($option) => [
                        'id' => $option->id,
                        'label' => $option->label,
                        'is_correct' => $option->is_correct,
                    ])->all(),
                ],
                [
                    'id' => $secondQuestion->id,
                    'type' => 'mcq',
                    'prompt' => 'Updated mcq question',
                    'points' => 50,
                    'options' => $secondQuestion->options->map(fn ($option, $index) => [
                        'id' => $option->id,
                        'label' => $option->label,
                        'is_correct' => $index === 1,
                    ])->all(),
                ],
            ],
        ]))
        ->assertRedirect(route('admin.exams.show', $exam));

    $exam->refresh()->load('questions.options');

    expect($exam->title)->toBe('Physics Final Updated')
        ->and((float) $exam->max_score)->toBe(60.0)
        ->and($exam->questions[0]->id)->toBe($firstQuestion->id)
        ->and($exam->questions[1]->id)->toBe($secondQuestion->id);
});

it('rejects invalid exam authoring payloads', function () {
    $teacher = User::factory()->teacher()->create();
    $group = TeachingGroup::create(['name' => 'Physics Group']);

    $this->actingAs($teacher)
        ->from(route('admin.exams.create'))
        ->post(route('admin.exams.store'), examPayload($group, [
            'questions' => [
                [
                    'type' => 'mcq',
                    'prompt' => 'Broken question',
                    'points' => 10,
                    'options' => [
                        ['label' => 'Only one option', 'is_correct' => true],
                    ],
                ],
            ],
        ]))
        ->assertRedirect(route('admin.exams.create'))
        ->assertSessionHasErrors(['questions.0.options']);
});

it('renders exam authoring details on the admin show page', function () {
    $teacher = User::factory()->teacher()->create();
    $group = TeachingGroup::create(['name' => 'Physics Group']);

    $this->actingAs($teacher)->post(route('admin.exams.store'), examPayload($group));
    $exam = Exam::query()->firstOrFail();

    $this->actingAs($teacher)
        ->get(route('admin.exams.show', $exam))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Exams/Show')
            ->where('exam.max_score', '50.00')
            ->where('exam.max_allowed_time', '90 min')
            ->has('exam.questions', 2));
});
