<?php

namespace Modules\Exams\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Academics\Models\TeachingGroup;
use Modules\Exams\Actions\UpsertExam;
use Modules\Exams\Models\Exam;
use Modules\Exams\Models\ExamResult;
use Modules\People\Models\Student;

class ExamsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mathGroup = TeachingGroup::query()->where('name', 'Math Grade 10 - Group A')->firstOrFail();
        $students = Student::query()->whereIn('code', ['ST-001', 'ST-002', 'ST-003', 'ST-004'])->get()->keyBy('code');
        $startAt = now()->subDay()->setTime(9, 0);

        $mathExam = app(UpsertExam::class)->handle(
            [
                'teaching_group_id' => $mathGroup->id,
                'title' => 'Algebra Quiz 1',
                'start_at' => $startAt,
                'end_at' => (clone $startAt)->addHour(),
                'max_allowed_time' => 60,
                'notes' => 'Short quiz covering equations and inequalities.',
            ],
            [
                [
                    'type' => 'true_false',
                    'prompt' => 'An equation can have more than one valid solution.',
                    'points' => 20,
                    'options' => [
                        ['label' => 'True', 'is_correct' => true],
                        ['label' => 'False', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'mcq',
                    'prompt' => 'What is the value of x in 2x + 6 = 14?',
                    'points' => 30,
                    'options' => [
                        ['label' => '2', 'is_correct' => false],
                        ['label' => '3', 'is_correct' => false],
                        ['label' => '4', 'is_correct' => true],
                        ['label' => '5', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'mcq',
                    'prompt' => 'Which graph best represents a linear function?',
                    'points' => 50,
                    'options' => [
                        ['label' => 'A straight line', 'is_correct' => true],
                        ['label' => 'A circle', 'is_correct' => false],
                        ['label' => 'A parabola', 'is_correct' => false],
                    ],
                ],
            ],
            Exam::query()->firstOrNew([
                'teaching_group_id' => $mathGroup->id,
                'title' => 'Algebra Quiz 1',
            ]),
        );

        collect([
            'ST-001' => 94,
            'ST-002' => 88,
            'ST-003' => 76,
            'ST-004' => 91,
        ])->each(function (int $score, string $code) use ($mathExam, $students): void {
            ExamResult::updateOrCreate(
                ['exam_id' => $mathExam->id, 'student_id' => $students[$code]->id],
                ExamResult::factory()->forExamAndStudent($mathExam, $students[$code])->make([
                    'score' => $score,
                    'notes' => $score >= 90 ? 'Excellent progress.' : 'Review mistakes before next session.',
                ])->getAttributes(),
            );
        });
    }
}
