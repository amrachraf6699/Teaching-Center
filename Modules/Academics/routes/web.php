<?php

use Illuminate\Support\Facades\Route;
use Modules\Academics\Http\Controllers\AttendanceController;
use Modules\Academics\Http\Controllers\GroupSessionController;
use Modules\Academics\Http\Controllers\TeachingGroupController;

Route::middleware(['auth', 'role:teacher'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::resource('groups', TeachingGroupController::class)->only(['index', 'create', 'store']);
    Route::resource('sessions', GroupSessionController::class)->only(['index', 'create', 'store']);
    Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
});
