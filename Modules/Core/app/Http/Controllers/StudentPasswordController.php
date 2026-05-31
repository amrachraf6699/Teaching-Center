<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class StudentPasswordController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Student/Password', [
            'action' => route('student.password.update'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $request->user()->forceFill([
            'password' => Hash::make($data['password']),
        ])->save();

        return redirect()->route('student.password.edit')->with('status', __('flash.password.updated'));
    }
}
