<?php

use Illuminate\Support\Facades\Route;
use Modules\Exports\Http\Controllers\ExportDashboardController;
use Modules\Exports\Http\Controllers\ResourceExportController;

Route::middleware(['web', 'auth', 'role:teacher'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('exports', ExportDashboardController::class)->name('exports.index');
    Route::get('parents/export/{format}', [ResourceExportController::class, 'parents'])->name('parents.export');
    Route::get('students/export/{format}', [ResourceExportController::class, 'students'])->name('students.export');
    Route::get('groups/export/{format}', [ResourceExportController::class, 'groups'])->name('groups.export');
    Route::get('timetables/export/{format}', [ResourceExportController::class, 'timetables'])->name('timetables.export');
    Route::get('sessions/export/{format}', [ResourceExportController::class, 'sessions'])->name('sessions.export');
    Route::get('exams/export/{format}', [ResourceExportController::class, 'exams'])->name('exams.export');
});
