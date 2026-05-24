<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Academics\Models\GroupSession;
use Modules\Academics\Models\TeachingGroup;
use Modules\Academics\Models\Timetable;
use Modules\Exams\Models\Exam;
use Modules\People\Models\Student;

uses(RefreshDatabase::class);

it('exports filtered students as csv and pdf', function () {
    $teacher = User::factory()->teacher()->create();
    $targetParent = User::factory()->parent()->create(['name' => 'Mona Parent']);
    $otherParent = User::factory()->parent()->create(['name' => 'Karim Parent']);
    $targetGroup = TeachingGroup::create(['name' => 'Science A', 'subject' => 'Science']);
    $otherGroup = TeachingGroup::create(['name' => 'Math B', 'subject' => 'Math']);

    $matchingStudent = Student::create([
        'parent_id' => $targetParent->id,
        'name' => 'Omar Filtered',
        'code' => 'ST-701',
        'is_active' => true,
    ]);
    $matchingStudent->groups()->sync([$targetGroup->id]);

    $hiddenStudent = Student::create([
        'parent_id' => $otherParent->id,
        'name' => 'Hidden Student',
        'code' => 'ST-702',
        'is_active' => false,
    ]);
    $hiddenStudent->groups()->sync([$otherGroup->id]);

    $csv = $this->actingAs($teacher)->get(route('admin.students.export', [
        'format' => 'csv',
        'search' => 'Omar',
        'status' => 'active',
        'parent_id' => $targetParent->id,
        'group_id' => $targetGroup->id,
    ]));

    $csv->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    expect($csv->streamedContent())
        ->toContain('Omar Filtered')
        ->not->toContain('Hidden Student');

    $pdf = $this->actingAs($teacher)->get(route('admin.students.export', [
        'format' => 'pdf',
        'search' => 'Omar',
        'status' => 'active',
        'parent_id' => $targetParent->id,
        'group_id' => $targetGroup->id,
    ]));

    $pdf->assertOk();
    expect((string) $pdf->headers->get('content-type'))->toContain('application/pdf');
});

it('exports filtered parents groups sessions and exams as csv', function () {
    $teacher = User::factory()->teacher()->create();

    $parentWithStudents = User::factory()->parent()->create([
        'name' => 'Parent Match',
        'email' => 'match@example.test',
    ]);
    $parentWithoutStudents = User::factory()->parent()->create([
        'name' => 'Parent Hidden',
        'email' => 'hidden@example.test',
    ]);

    $student = Student::create([
        'parent_id' => $parentWithStudents->id,
        'name' => 'Student One',
        'code' => 'ST-801',
    ]);

    $activeGroup = TeachingGroup::create([
        'name' => 'Physics Match',
        'subject' => 'Physics',
        'is_active' => true,
    ]);
    $inactiveGroup = TeachingGroup::create([
        'name' => 'History Hidden',
        'subject' => 'History',
        'is_active' => false,
    ]);
    $activeGroup->students()->sync([$student->id]);

    $matchingSession = GroupSession::create([
        'teaching_group_id' => $activeGroup->id,
        'title' => 'Session Match',
        'starts_at' => now()->addDay(),
    ]);
    GroupSession::create([
        'teaching_group_id' => $inactiveGroup->id,
        'title' => 'Session Hidden',
        'starts_at' => now()->addDays(2),
    ]);

    $matchingExam = Exam::create([
        'teaching_group_id' => $activeGroup->id,
        'title' => 'Exam Match',
        'start_at' => now(),
        'end_at' => now()->copy()->addHour(),
        'max_allowed_time' => 60,
        'max_score' => 100,
    ]);
    Exam::create([
        'teaching_group_id' => $inactiveGroup->id,
        'title' => 'Exam Hidden',
        'start_at' => now()->addDay(),
        'end_at' => now()->addDay()->addHour(),
        'max_allowed_time' => 60,
        'max_score' => 100,
    ]);

    $parentsCsv = $this->actingAs($teacher)->get(route('admin.parents.export', [
        'format' => 'csv',
        'search' => 'Match',
        'children' => 'with',
    ]));
    expect($parentsCsv->streamedContent())
        ->toContain('Parent Match')
        ->not->toContain('Parent Hidden');

    $groupsCsv = $this->actingAs($teacher)->get(route('admin.groups.export', [
        'format' => 'csv',
        'search' => 'Physics',
        'status' => 'active',
    ]));
    expect($groupsCsv->streamedContent())
        ->toContain('Physics Match')
        ->not->toContain('History Hidden');

    $sessionsCsv = $this->actingAs($teacher)->get(route('admin.sessions.export', [
        'format' => 'csv',
        'search' => 'Match',
        'group' => $activeGroup->id,
    ]));
    expect($sessionsCsv->streamedContent())
        ->toContain($matchingSession->title)
        ->not->toContain('Session Hidden');

    $examsCsv = $this->actingAs($teacher)->get(route('admin.exams.export', [
        'format' => 'csv',
        'search' => 'Match',
        'group' => $activeGroup->id,
    ]));
    expect($examsCsv->streamedContent())
        ->toContain($matchingExam->title)
        ->not->toContain('Exam Hidden');
});

it('exports filtered timetables as a day-first weekly grid', function () {
    $teacher = User::factory()->teacher()->create();

    $matchingGroup = TeachingGroup::create([
        'name' => 'Physics Morning',
        'subject' => 'Physics',
    ]);
    $hiddenGroup = TeachingGroup::create([
        'name' => 'History Evening',
        'subject' => 'History',
    ]);

    $matchingTimetable = Timetable::create([
        'teaching_group_id' => $matchingGroup->id,
    ]);
    $matchingTimetable->entries()->createMany([
        ['day_of_week' => 'saturday', 'starts_at' => '08:00', 'ends_at' => '09:00'],
        ['day_of_week' => 'monday', 'starts_at' => '09:00', 'ends_at' => '10:30'],
        ['day_of_week' => 'wednesday', 'starts_at' => '11:00', 'ends_at' => '12:00'],
    ]);

    $hiddenTimetable = Timetable::create([
        'teaching_group_id' => $hiddenGroup->id,
    ]);
    $hiddenTimetable->entries()->createMany([
        ['day_of_week' => 'tuesday', 'starts_at' => '17:00', 'ends_at' => '18:00'],
    ]);

    $csv = $this->actingAs($teacher)->get(route('admin.timetables.export', [
        'format' => 'csv',
        'search' => 'Physics',
        'group_id' => $matchingGroup->id,
    ]));

    $csv->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    expect($csv->streamedContent())
        ->toContain('Saturday,Sunday,Monday,Tuesday,Wednesday,Thursday,Friday')
        ->toContain('Physics Morning (08:00 - 09:00)')
        ->toContain('Physics Morning (09:00 - 10:30)')
        ->toContain('Physics Morning (11:00 - 12:00)')
        ->not->toContain('History Evening');

    $pdf = $this->actingAs($teacher)->get(route('admin.timetables.export', [
        'format' => 'pdf',
        'search' => 'Physics',
        'group_id' => $matchingGroup->id,
    ]));

    $pdf->assertOk();
    expect((string) $pdf->headers->get('content-type'))->toContain('application/pdf');
});
