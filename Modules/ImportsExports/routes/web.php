<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'role:teacher'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('imports-exports', fn () => Inertia::render('Admin/ImportsExports/Index'))->name('imports-exports.index');
});
