<?php

use Illuminate\Support\Facades\Route;
use Modules\People\Http\Controllers\ParentAccountController;
use Modules\People\Http\Controllers\StudentController;

Route::middleware(['auth', 'role:teacher'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('parents/export/{format}', [ParentAccountController::class, 'export'])->name('parents.export');
    Route::resource('parents', ParentAccountController::class);
    Route::get('students/export/{format}', [StudentController::class, 'export'])->name('students.export');
    Route::patch('students/{student}/status', [StudentController::class, 'toggleStatus'])->name('students.toggle-status');
    Route::resource('students', StudentController::class);
});
