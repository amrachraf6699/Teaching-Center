<?php

namespace Modules\Academics\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Academics\Models\Attendance;
use Modules\Academics\Models\GroupSession;
use Modules\Academics\Models\TeachingGroup;
use Modules\People\Models\Student;

class AcademicsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = Student::query()->whereIn('code', [
            'ST-001',
            'ST-002',
            'ST-003',
            'ST-004',
            'ST-005',
            'ST-006',
        ])->get()->keyBy('code');

        $mathGroup = TeachingGroup::updateOrCreate(
            ['name' => 'Math Grade 10 - Group A'],
            TeachingGroup::factory()->make([
                'name' => 'Math Grade 10 - Group A',
                'subject' => 'Mathematics',
                'level' => 'Grade 10',
                'description' => 'Core algebra and geometry group.',
                'is_active' => true,
            ])->getAttributes(),
        );

        $scienceGroup = TeachingGroup::updateOrCreate(
            ['name' => 'Science Grade 9 - Group B'],
            TeachingGroup::factory()->make([
                'name' => 'Science Grade 9 - Group B',
                'subject' => 'Science',
                'level' => 'Grade 9',
                'description' => 'Physics and chemistry foundations.',
                'is_active' => true,
            ])->getAttributes(),
        );

        $mathGroup->students()->sync([
            $students['ST-001']->id,
            $students['ST-002']->id,
            $students['ST-003']->id,
            $students['ST-004']->id,
        ]);

        $scienceGroup->students()->sync([
            $students['ST-003']->id,
            $students['ST-004']->id,
            $students['ST-005']->id,
            $students['ST-006']->id,
        ]);

        $mathSession = GroupSession::updateOrCreate(
            ['teaching_group_id' => $mathGroup->id, 'title' => 'Linear Equations Practice'],
            GroupSession::factory()->forGroup($mathGroup)->make([
                'title' => 'Linear Equations Practice',
                'starts_at' => now()->subDays(2)->setTime(17, 0),
                'ends_at' => now()->subDays(2)->setTime(18, 30),
                'notes' => 'Practice session with attendance recorded.',
            ])->getAttributes(),
        );

        GroupSession::updateOrCreate(
            ['teaching_group_id' => $scienceGroup->id, 'title' => 'Chemical Reactions Review'],
            GroupSession::factory()->forGroup($scienceGroup)->make([
                'title' => 'Chemical Reactions Review',
                'starts_at' => now()->addDays(1)->setTime(18, 0),
                'ends_at' => now()->addDays(1)->setTime(19, 30),
                'notes' => 'Upcoming revision session.',
            ])->getAttributes(),
        );

        $mathGroup->students()->get()->each(function (Student $student, int $index) use ($mathSession): void {
            Attendance::updateOrCreate(
                ['teaching_session_id' => $mathSession->id, 'student_id' => $student->id],
                Attendance::factory()->forSessionAndStudent($mathSession, $student)->make([
                    'status' => $index === 2 ? 'late' : 'present',
                    'notes' => $index === 2 ? 'Arrived 10 minutes late.' : null,
                ])->getAttributes(),
            );
        });
    }
}
