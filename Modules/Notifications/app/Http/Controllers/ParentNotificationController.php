<?php

namespace Modules\Notifications\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Notifications\Models\ParentNotification;
use Modules\People\Models\Student;

class ParentNotificationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'type' => ['nullable', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $student = Student::findOrFail($data['student_id']);

        ParentNotification::create([
            'parent_id' => $student->parent_id,
            'student_id' => $student->id,
            'type' => $data['type'] ?? 'general',
            'title' => $data['title'],
            'body' => $data['body'],
        ]);

        return back()->with('status', 'Notification created.');
    }
}
