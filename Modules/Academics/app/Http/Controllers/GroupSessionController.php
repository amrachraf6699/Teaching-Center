<?php

namespace Modules\Academics\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Academics\Models\GroupSession;
use Modules\Academics\Models\TeachingGroup;

class GroupSessionController extends Controller
{
    public function index(): View
    {
        return view('academics::sessions.index', [
            'sessions' => GroupSession::query()->with('group')->latest('starts_at')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('academics::sessions.create', [
            'groups' => TeachingGroup::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'teaching_group_id' => ['required', 'exists:teaching_groups,id'],
            'title' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'notes' => ['nullable', 'string'],
        ]);

        GroupSession::create($data);

        return redirect()->route('admin.sessions.index')->with('status', 'Session created.');
    }
}
