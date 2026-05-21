<?php

namespace Modules\People\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ParentAccountController extends Controller
{
    public function index(): View
    {
        return view('people::parents.index', [
            'parents' => User::query()->where('role', 'parent')->withCount('children')->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('people::parents.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'parent',
        ]);

        return redirect()->route('admin.parents.index')->with('status', 'Parent account created.');
    }
}
