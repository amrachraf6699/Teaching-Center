<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:teacher'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::view('imports-exports', 'importsexports::index')->name('imports-exports.index');
});
