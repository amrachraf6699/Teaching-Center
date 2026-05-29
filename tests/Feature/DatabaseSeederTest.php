<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Modules\Academics\Models\Attendance;
use Modules\Academics\Models\GroupSession;
use Modules\Academics\Models\TeachingGroup;
use Modules\Core\Settings\GeneralSettings;
use Modules\Exams\Models\Exam;
use Modules\Exams\Models\ExamResult;
use Modules\People\Models\Student;

uses(RefreshDatabase::class);

it('seeds usable data for every Teachify domain table', function () {
    $this->seed(DatabaseSeeder::class);
    $settings = app(GeneralSettings::class);

    expect(User::query()->where('role', 'teacher')->count())->toBe(1)
        ->and(User::query()->where('role', 'parent')->count())->toBe(3)
        ->and(User::query()->where('role', 'student')->count())->toBe(6)
        ->and(Student::count())->toBe(6)
        ->and(TeachingGroup::count())->toBe(2)
        ->and(DB::table('group_student')->count())->toBe(8)
        ->and(GroupSession::count())->toBe(2)
        ->and(Attendance::count())->toBe(4)
        ->and(Exam::count())->toBe(1)
        ->and(ExamResult::count())->toBe(4)
        ->and(DatabaseNotification::count())->toBe(10)
        ->and($settings->name)->toBe('Teachify')
        ->and($settings->contact_email)->toBe('teacher@teachify.test')
        ->and($settings->timezone)->toBe('Africa/Cairo')
        ->and(DB::table('settings')->where('group', 'general')->count())->toBe(16)
        ->and(DB::table('cache')->where('key', 'teachify_seeded_at')->exists())->toBeTrue()
        ->and(DB::table('cache_locks')->where('key', 'teachify_seed_lock')->exists())->toBeTrue()
        ->and(DB::table('activity_log')->count())->toBeGreaterThan(0);
});
