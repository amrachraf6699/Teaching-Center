<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\AuthController;
use Modules\Core\Http\Controllers\ParentPortalController;
use Modules\Core\Http\Controllers\SettingsController;
use Modules\Core\Http\Controllers\TeacherDashboardController;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/admin/dashboard', TeacherDashboardController::class)
    ->middleware(['auth', 'role:teacher'])
    ->name('admin.dashboard');

Route::middleware(['auth', 'role:teacher'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
});

Route::get('/parent/dashboard', ParentPortalController::class)
    ->middleware(['auth', 'role:parent'])
    ->name('parent.dashboard');
