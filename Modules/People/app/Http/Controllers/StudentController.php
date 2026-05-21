<?php

namespace Modules\People\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\People\Models\Student;

class StudentController extends Controller
{
    public function index(): View
    {
        return view('people::students.index', [
            'students' => Student::query()->with('parent')->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('people::students.create', [
            'parents' => User::query()->where('role', 'parent')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'parent_id' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', 'unique:students,code'],
            'phone' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $parent = User::query()->whereKey($data['parent_id'])->where('role', 'parent')->firstOrFail();
        $data['parent_id'] = $parent->id;

        Student::create($data);

        return redirect()->route('admin.students.index')->with('status', 'Student created.');
    }
}
