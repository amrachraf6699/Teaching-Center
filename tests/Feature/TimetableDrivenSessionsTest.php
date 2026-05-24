<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Academics\Actions\GenerateSessionsFromTimetables;
use Modules\Academics\Models\Attendance;
use Modules\Academics\Models\GroupSession;
use Modules\Academics\Models\TeachingGroup;
use Modules\Academics\Models\Timetable;
use Modules\People\Models\Student;

uses(RefreshDatabase::class);

it('generates session instances immediately when a timetable is created or updated', function () {
    $this->travelTo(now()->setDate(2026, 5, 25)->setTime(9, 0));

    $teacher = User::factory()->teacher()->create();
    $group = TeachingGroup::create([
        'name' => 'Physics Group',
        'subject' => 'Physics',
    ]);

    $entries = [
        ['day' => 'monday', 'active' => true, 'starts_at' => '15:00', 'ends_at' => '16:30'],
        ['day' => 'tuesday', 'active' => false, 'starts_at' => '', 'ends_at' => ''],
        ['day' => 'wednesday', 'active' => true, 'starts_at' => '17:00', 'ends_at' => '18:00'],
        ['day' => 'thursday', 'active' => false, 'starts_at' => '', 'ends_at' => ''],
        ['day' => 'friday', 'active' => false, 'starts_at' => '', 'ends_at' => ''],
        ['day' => 'saturday', 'active' => false, 'starts_at' => '', 'ends_at' => ''],
        ['day' => 'sunday', 'active' => false, 'starts_at' => '', 'ends_at' => ''],
    ];

    $this->actingAs($teacher)
        ->post(route('admin.timetables.store'), [
            'teaching_group_id' => $group->id,
            'entries' => $entries,
        ])
        ->assertRedirect(route('admin.timetables.index'));

    $timetable = Timetable::query()->where('teaching_group_id', $group->id)->firstOrFail();
    $generatedSessions = GroupSession::query()
        ->where('source_type', 'timetable')
        ->orderBy('starts_at')
        ->get();

    expect($generatedSessions)->toHaveCount(2)
        ->and($generatedSessions[0]->title)->toBe('Physics Group Session')
        ->and($generatedSessions[0]->session_date?->toDateString())->toBe('2026-05-25')
        ->and($generatedSessions[1]->session_date?->toDateString())->toBe('2026-05-27');

    $wednesdaySession = $generatedSessions[1];
    $student = Student::factory()->create();
    $group->students()->sync([$student->id]);
    Attendance::create([
        'teaching_session_id' => $wednesdaySession->id,
        'student_id' => $student->id,
        'status' => 'present',
    ]);

    $updatedEntries = [
        ['day' => 'monday', 'active' => false, 'starts_at' => '', 'ends_at' => ''],
        ['day' => 'tuesday', 'active' => false, 'starts_at' => '', 'ends_at' => ''],
        ['day' => 'wednesday', 'active' => true, 'starts_at' => '18:30', 'ends_at' => '19:30'],
        ['day' => 'thursday', 'active' => false, 'starts_at' => '', 'ends_at' => ''],
        ['day' => 'friday', 'active' => true, 'starts_at' => '12:00', 'ends_at' => '13:00'],
        ['day' => 'saturday', 'active' => false, 'starts_at' => '', 'ends_at' => ''],
        ['day' => 'sunday', 'active' => false, 'starts_at' => '', 'ends_at' => ''],
    ];

    $this->actingAs($teacher)
        ->put(route('admin.timetables.update', $timetable), [
            'entries' => $updatedEntries,
        ])
        ->assertRedirect(route('admin.timetables.show', $timetable));

    expect(GroupSession::query()->whereKey($wednesdaySession->id)->firstOrFail()->starts_at?->format('H:i'))->toBe('17:00')
        ->and(GroupSession::query()->where('source_type', 'timetable')->whereDate('session_date', '2026-05-25')->exists())->toBeFalse()
        ->and(GroupSession::query()->where('source_type', 'timetable')->whereDate('session_date', '2026-05-29')->exists())->toBeTrue();
});

it('runs the timetable generation command idempotently for the next 7 days', function () {
    $this->travelTo(now()->setDate(2026, 5, 29)->setTime(10, 0));

    $group = TeachingGroup::create([
        'name' => 'Math Group',
        'subject' => 'Math',
    ]);

    $timetable = Timetable::create([
        'teaching_group_id' => $group->id,
    ]);
    $timetable->entries()->create([
        'day_of_week' => 'friday',
        'starts_at' => '14:00',
        'ends_at' => '15:30',
    ]);

    $this->artisan('sessions:generate-from-timetables')->assertSuccessful();
    $this->artisan('sessions:generate-from-timetables')->assertSuccessful();

    $generatedSessions = GroupSession::query()
        ->where('source_type', 'timetable')
        ->get();

    expect($generatedSessions)->toHaveCount(1)
        ->and($generatedSessions->first()->session_date?->toDateString())->toBe('2026-05-29');
});
