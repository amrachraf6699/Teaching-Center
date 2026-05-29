<?php

use Illuminate\Support\Facades\Route;
use Modules\Exams\Http\Controllers\ExamController;
use Modules\Exams\Http\Controllers\ExamResultController;
use Modules\Exams\Http\Controllers\StudentExamAttemptController;
use Modules\Exams\Http\Controllers\StudentExamController;

Route::middleware(['auth', 'role:teacher'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('exams/export/{format}', [ExamController::class, 'export'])->name('exams.export');
    Route::resource('exams', ExamController::class);
    Route::post('exams/{exam}/results', [ExamResultController::class, 'store'])->name('exam-results.store');
});

Route::middleware(['auth', 'role:student'])->prefix('student/exams')->name('student.exams.')->group(function (): void {
    Route::get('/', [StudentExamController::class, 'index'])->name('index');
    Route::get('{exam}', [StudentExamController::class, 'show'])->name('show');
    Route::post('{exam}/start', [StudentExamController::class, 'start'])->name('start');
    Route::get('{exam}/attempts/{attempt}', [StudentExamAttemptController::class, 'show'])->name('attempt.show');
    Route::post('{exam}/attempts/{attempt}/answer', [StudentExamAttemptController::class, 'saveAnswer'])->name('attempt.answer');
    Route::post('{exam}/attempts/{attempt}/submit', [StudentExamAttemptController::class, 'submit'])->name('attempt.submit');
});
