<?php

namespace Modules\Exams\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Academics\Models\TeachingGroup;
use Modules\Exams\Models\Exam;

class ExamController extends Controller
{
    public function index(): View
    {
        return view('exams::exams.index', [
            'exams' => Exam::query()->with('group')->latest('exam_date')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('exams::exams.create', [
            'groups' => TeachingGroup::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'teaching_group_id' => ['required', 'exists:teaching_groups,id'],
            'title' => ['required', 'string', 'max:255'],
            'exam_date' => ['required', 'date'],
            'max_score' => ['required', 'numeric', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        Exam::create($data);

        return redirect()->route('admin.exams.index')->with('status', 'Exam created.');
    }
}
