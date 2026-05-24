<?php

use Illuminate\Support\Facades\Route;
use Modules\Academics\Http\Controllers\AttendanceController;
use Modules\Academics\Http\Controllers\GroupSessionController;
use Modules\Academics\Http\Controllers\TeachingGroupController;
use Modules\Academics\Http\Controllers\TimetableController;

Route::middleware(['auth', 'role:teacher'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('groups/export/{format}', [TeachingGroupController::class, 'export'])->name('groups.export');
    Route::resource('groups', TeachingGroupController::class);
    Route::get('timetables/export/{format}', [TimetableController::class, 'export'])->name('timetables.export');
    Route::resource('timetables', TimetableController::class);
    Route::get('sessions/export/{format}', [GroupSessionController::class, 'export'])->name('sessions.export');
    Route::resource('sessions', GroupSessionController::class);
    Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
});
