<?php

use Illuminate\Support\Facades\Route;
use Modules\Imports\Http\Controllers\ImportController;

Route::middleware(['web', 'auth', 'role:teacher'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('imports', [ImportController::class, 'index'])->name('imports.index');
    Route::get('imports/templates/{type}/{format?}', [ImportController::class, 'template'])->name('imports.template');
    Route::post('imports/{type}', [ImportController::class, 'store'])->name('imports.store');
    Route::get('imports/{batch}', [ImportController::class, 'show'])->name('imports.show');
});
