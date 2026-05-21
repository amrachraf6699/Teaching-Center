<?php

use Illuminate\Support\Facades\Route;
use Modules\Notifications\Http\Controllers\ParentNotificationController;

Route::middleware(['auth', 'role:teacher'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::post('notifications', [ParentNotificationController::class, 'store'])->name('notifications.store');
});
