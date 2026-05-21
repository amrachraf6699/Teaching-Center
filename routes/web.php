<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return auth()->user()->role === 'parent'
        ? redirect()->route('parent.dashboard')
        : redirect()->route('admin.dashboard');
});
