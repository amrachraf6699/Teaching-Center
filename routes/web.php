<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return match (auth()->user()->role) {
        'parent' => redirect()->route('parent.dashboard'),
        'student' => redirect()->route('student.home'),
        default => redirect()->route('admin.dashboard'),
    };
});
