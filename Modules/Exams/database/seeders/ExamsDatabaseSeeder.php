<?php

namespace Modules\Exams\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Academics\Models\TeachingGroup;
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

        $mathExam = Exam::updateOrCreate(
            ['teaching_group_id' => $mathGroup->id, 'title' => 'Algebra Quiz 1'],
            Exam::factory()->forGroup($mathGroup)->make([
                'title' => 'Algebra Quiz 1',
                'exam_date' => now()->subDay()->toDateString(),
                'max_score' => 100,
                'notes' => 'Short quiz covering equations and inequalities.',
            ])->getAttributes(),
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
