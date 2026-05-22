<?php

use Illuminate\Support\Facades\Route;
use Modules\Exams\Http\Controllers\ExamController;
use Modules\Exams\Http\Controllers\ExamResultController;

Route::middleware(['auth', 'role:teacher'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::resource('exams', ExamController::class);
    Route::post('exams/{exam}/results', [ExamResultController::class, 'store'])->name('exam-results.store');
});
