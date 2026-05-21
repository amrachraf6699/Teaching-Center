<?php

use Illuminate\Support\Facades\Route;
use Modules\People\Http\Controllers\ParentAccountController;
use Modules\People\Http\Controllers\StudentController;

Route::middleware(['auth', 'role:teacher'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::resource('parents', ParentAccountController::class)->only(['index', 'create', 'store']);
    Route::resource('students', StudentController::class)->only(['index', 'create', 'store']);
});
