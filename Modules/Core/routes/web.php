<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\AuthController;
use Modules\Core\Http\Controllers\ParentAttendanceController;
use Modules\Core\Http\Controllers\ParentChildPdfController;
use Modules\Core\Http\Controllers\ParentExamsController;
use Modules\Core\Http\Controllers\PortalNotificationsController;
use Modules\Core\Http\Controllers\ParentPortalController;
use Modules\Core\Http\Controllers\SettingsController;
use Modules\Core\Http\Controllers\StudentAttendanceScannerController;
use Modules\Core\Http\Controllers\StudentHomeController;
use Modules\Core\Http\Controllers\StudentPasswordController;
use Modules\Core\Http\Controllers\StudentSessionsController;
use Modules\Core\Http\Controllers\TeacherDashboardController;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.store');
    Route::get('/student/login', [AuthController::class, 'studentLogin'])->name('student.login');
    Route::post('/student/login', [AuthController::class, 'authenticateStudent'])->name('student.login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/admin/dashboard', TeacherDashboardController::class)
    ->middleware(['auth', 'role:teacher'])
    ->name('admin.dashboard');

Route::middleware(['auth', 'role:teacher'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
});

Route::middleware(['auth', 'role:parent'])->prefix('parent')->name('parent.')->group(function (): void {
    Route::get('dashboard', ParentPortalController::class)->name('dashboard');
    Route::get('attendance', ParentAttendanceController::class)->name('attendance');
    Route::get('exams', ParentExamsController::class)->name('exams');
    Route::get('notifications', [PortalNotificationsController::class, '__invoke'])->name('notifications');
    Route::post('notifications/mark-read', [PortalNotificationsController::class, 'markRead'])->name('notifications.mark-read');
    Route::get('child/{student}/pdf', ParentChildPdfController::class)->name('child.pdf');
});

Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function (): void {
    Route::get('home', StudentHomeController::class)->name('home');
    Route::get('dashboard', StudentHomeController::class)->name('dashboard');
    Route::get('sessions', StudentSessionsController::class)->name('sessions');
    Route::get('password', [StudentPasswordController::class, 'edit'])->name('password.edit');
    Route::put('password', [StudentPasswordController::class, 'update'])->name('password.update');
    Route::get('scan-attendance', StudentAttendanceScannerController::class)->name('scan-attendance');
});
