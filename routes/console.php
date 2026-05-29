<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Modules\Exams\Actions\DispatchScheduledExamReminders;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('sessions:generate-from-timetables')
    ->fridays()
    ->at('00:05');

Schedule::call(fn () => app(DispatchScheduledExamReminders::class)->handle())
    ->name('dispatch-scheduled-exam-reminders')
    ->everyMinute()
    ->withoutOverlapping();