<?php

use Illuminate\Support\Facades\Route;
use Modules\Academics\Http\Controllers\AttendanceController;
use Modules\Academics\Http\Controllers\GroupSessionController;
use Modules\Academics\Http\Controllers\StudentSessionAttendanceController;
use Modules\Academics\Http\Controllers\TeachingGroupController;
use Modules\Academics\Http\Controllers\TimetableController;

Route::middleware(['auth', 'role:teacher'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::resource('groups', TeachingGroupController::class);
    Route::resource('timetables', TimetableController::class);
    Route::resource('sessions', GroupSessionController::class);
    Route::post('sessions/{session}/regenerate-attendance-code', [GroupSessionController::class, 'regenerateAttendanceCode'])->name('sessions.regenerate-attendance-code');
    Route::patch('sessions/{session}/attendance-entry', [GroupSessionController::class, 'updateAttendanceEntry'])->name('sessions.update-attendance-entry');
    Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
});

Route::get('student/sessions/{session}/scan', [StudentSessionAttendanceController::class, 'show'])
    ->middleware('signed')
    ->name('student.sessions.scan');

Route::post('student/sessions/{session}/attendance', [StudentSessionAttendanceController::class, 'store'])
    ->middleware(['signed', 'auth', 'role:student'])
    ->name('student.sessions.attendance.store');

Route::post('student/attendance/lookup-by-code', [StudentSessionAttendanceController::class, 'lookupByCode'])
    ->middleware(['auth', 'role:student'])
    ->name('student.attendance.lookup-by-code');
