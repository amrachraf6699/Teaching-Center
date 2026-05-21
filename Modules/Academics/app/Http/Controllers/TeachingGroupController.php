<?php

namespace Modules\Academics\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Academics\Models\TeachingGroup;
use Modules\People\Models\Student;

class TeachingGroupController extends Controller
{
    public function index(): View
    {
        return view('academics::groups.index', [
            'groups' => TeachingGroup::query()->withCount('students')->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('academics::groups.create', [
            'students' => Student::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'level' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['exists:students,id'],
        ]);

        $studentIds = $data['student_ids'] ?? [];
        unset($data['student_ids']);

        $group = TeachingGroup::create($data);
        $group->students()->sync($studentIds);

        return redirect()->route('admin.groups.index')->with('status', 'Group created.');
    }
}
