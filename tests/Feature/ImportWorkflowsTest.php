<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use Modules\Academics\Models\Attendance;
use Modules\Academics\Models\GroupSession;
use Modules\Academics\Models\TeachingGroup;
use Modules\Academics\Models\TimetableEntry;
use Modules\Exports\Http\Controllers\ResourceExportController;
use Modules\Exams\Models\Exam;
use Modules\Exams\Models\ExamQuestion;
use Modules\Exams\Models\ExamResult;
use Modules\Imports\Models\ImportBatch;
use Modules\People\Models\Student;

uses(RefreshDatabase::class);

function csvUpload(string $name, string $contents): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, $contents);
}

it('registers preserved export route names from the Exports module', function () {
    expect(Route::getRoutes()->getByName('admin.students.export')->getActionName())->toContain(ResourceExportController::class)
        ->and(Route::getRoutes()->getByName('admin.groups.export')->getActionName())->toContain(ResourceExportController::class)
        ->and(Route::getRoutes()->getByName('admin.exams.export')->getActionName())->toContain(ResourceExportController::class);
});

it('keeps imports and exports on related index pages instead of center pages', function () {
    $teacher = User::factory()->teacher()->create();

    expect(Route::has('admin.imports.index'))->toBeFalse()
        ->and(Route::has('admin.exports.index'))->toBeFalse();

    $this->actingAs($teacher)
        ->get(route('admin.students.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Students/Index')
            ->where('importOptions.0.value', 'students')
            ->where('importOptions.1.value', 'enrollments')
            ->where('exportUrls.csv', route('admin.students.export', 'csv')));

    $this->actingAs($teacher)
        ->get(route('admin.exams.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Exams/Index')
            ->where('importOptions.0.value', 'exams')
            ->where('importOptions.1.value', 'exam_questions')
            ->where('importOptions.2.value', 'exam_results')
            ->where('exportUrls.pdf', route('admin.exams.export', 'pdf')));
});

it('imports parents and updates existing parents by email', function () {
    $teacher = User::factory()->teacher()->create();
    User::factory()->parent()->create(['name' => 'Old Name', 'email' => 'parent@example.test']);

    $response = $this->actingAs($teacher)->post(route('admin.imports.store', 'parents'), [
        'file' => csvUpload('parents.csv', "name,email,password\nUpdated Parent,parent@example.test,secret123\nNew Parent,new@example.test,secret123\n"),
    ]);

    $batch = ImportBatch::query()->latest()->first();
    $response->assertRedirect(route('admin.imports.show', $batch));

    expect($batch->status)->toBe('completed')
        ->and(User::query()->where('email', 'parent@example.test')->value('name'))->toBe('Updated Parent')
        ->and(User::query()->where('email', 'new@example.test')->where('role', 'parent')->exists())->toBeTrue();
});

it('imports students, groups, enrollments, sessions, attendance, exams, questions, and results', function () {
    $teacher = User::factory()->teacher()->create();
    User::factory()->parent()->create(['email' => 'parent@example.test', 'name' => 'Parent One']);

    $this->actingAs($teacher)->post(route('admin.imports.store', 'students'), [
        'file' => csvUpload('students.csv', "name,code,parent_email,phone,date_of_birth,notes,is_active,password\nStudent One,ST-900,parent@example.test,01000000000,2010-01-01,Imported,yes,secret123\n"),
    ])->assertSessionHasNoErrors();

    $this->actingAs($teacher)->post(route('admin.imports.store', 'groups'), [
        'file' => csvUpload('groups.csv', "name,subject,level,description,is_active\nMath A,Math,Grade 9,Imported group,yes\n"),
    ])->assertSessionHasNoErrors();

    $this->actingAs($teacher)->post(route('admin.imports.store', 'enrollments'), [
        'file' => csvUpload('enrollments.csv', "student_code,group_name\nST-900,Math A\n"),
    ])->assertSessionHasNoErrors();

    $this->actingAs($teacher)->post(route('admin.imports.store', 'timetables'), [
        'file' => csvUpload('timetables.csv', "group_name,day_of_week,starts_at,ends_at\nMath A,saturday,10:00,11:00\n"),
    ])->assertSessionHasNoErrors();

    $this->actingAs($teacher)->post(route('admin.imports.store', 'sessions'), [
        'file' => csvUpload('sessions.csv', "group_name,title,starts_at,ends_at,attendance_entry_enabled,notes\nMath A,Revision,2030-01-05 10:00,2030-01-05 11:00,yes,Imported session\n"),
    ])->assertSessionHasNoErrors();

    $this->actingAs($teacher)->post(route('admin.imports.store', 'attendance'), [
        'file' => csvUpload('attendance.csv', "session_title,session_starts_at,student_code,status,notes\nRevision,2030-01-05 10:00,ST-900,present,Checked\n"),
    ])->assertSessionHasNoErrors();

    $this->actingAs($teacher)->post(route('admin.imports.store', 'exams'), [
        'file' => csvUpload('exams.csv', "group_name,title,start_at,end_at,max_allowed_time,student_review_mode,notes\nMath A,Quiz 1,2030-01-06 10:00,2030-01-06 11:00,45,question_review,Imported exam\n"),
    ])->assertSessionHasNoErrors();

    $this->actingAs($teacher)->post(route('admin.imports.store', 'exam_questions'), [
        'file' => csvUpload('exam_questions.csv', "group_name,exam_title,exam_start_at,question_position,question_type,prompt,points,correct_boolean,option_position,option_label,is_correct\nMath A,Quiz 1,2030-01-06 10:00,1,mcq,Choose A,10,,1,A,yes\nMath A,Quiz 1,2030-01-06 10:00,1,mcq,Choose A,10,,2,B,no\n"),
    ])->assertSessionHasNoErrors();

    $this->actingAs($teacher)->post(route('admin.imports.store', 'exam_results'), [
        'file' => csvUpload('exam_results.csv', "group_name,exam_title,student_code,score,notes\nMath A,Quiz 1,ST-900,8,Manual result\n"),
    ])->assertSessionHasNoErrors();

    $student = Student::query()->where('code', 'ST-900')->first();
    $group = TeachingGroup::query()->where('name', 'Math A')->first();
    $session = GroupSession::query()->where('title', 'Revision')->first();
    $exam = Exam::query()->where('title', 'Quiz 1')->first();

    expect($student)->not->toBeNull()
        ->and($group->students()->whereKey($student->id)->exists())->toBeTrue()
        ->and(TimetableEntry::query()->where('day_of_week', 'saturday')->exists())->toBeTrue()
        ->and($session->manual_attendance_code)->not->toBeNull()
        ->and(Attendance::query()->where('teaching_session_id', $session->id)->where('student_id', $student->id)->exists())->toBeTrue()
        ->and($exam->student_review_mode)->toBe('question_review')
        ->and((float) $exam->fresh()->max_score)->toBe(10.0)
        ->and(ExamQuestion::query()->where('exam_id', $exam->id)->count())->toBe(1)
        ->and(ExamResult::query()->where('exam_id', $exam->id)->where('student_id', $student->id)->value('score'))->toBe('8.00');
});

it('stores row-level failures without rolling back successful rows', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)->post(route('admin.imports.store', 'groups'), [
        'file' => csvUpload('groups.csv', "name,subject,level,description,is_active\nValid Group,Science,Grade 8,Ok,yes\n,Science,Grade 8,Bad,yes\n"),
    ]);

    $batch = ImportBatch::query()->latest()->first();

    expect($batch->status)->toBe('completed_with_errors')
        ->and($batch->imported_rows)->toBe(1)
        ->and($batch->failed_rows)->toBe(1)
        ->and($batch->errors[0]['row'])->toBe(3)
        ->and(TeachingGroup::query()->where('name', 'Valid Group')->exists())->toBeTrue();
});

it('downloads every import template as csv', function () {
    $teacher = User::factory()->teacher()->create();

    foreach ([
        'parents',
        'students',
        'groups',
        'enrollments',
        'timetables',
        'sessions',
        'attendance',
        'exams',
        'exam_questions',
        'exam_results',
    ] as $type) {
        $response = $this->actingAs($teacher)->get(route('admin.imports.template', ['type' => $type, 'format' => 'csv']));

        $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        expect($response->streamedContent())->not->toBe('');
    }
});
