<?php

namespace Modules\Academics\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Academics\Models\Attendance;
use Modules\Academics\Models\GroupSession;
use Modules\Notifications\Services\PortalNotificationService;

class AttendanceController extends Controller
{
    public function store(Request $request, PortalNotificationService $notifications): RedirectResponse
    {
        $data = $request->validate([
            'teaching_session_id' => ['required', 'exists:teaching_sessions,id'],
            'student_id' => ['required', 'exists:students,id'],
            'status' => ['required', 'in:present,absent,late,excused'],
            'notes' => ['nullable', 'string'],
        ]);

        $session = GroupSession::query()->with('group.students')->findOrFail($data['teaching_session_id']);

        abort_unless($session->group->students->contains('id', (int) $data['student_id']), 422);

        $attendance = Attendance::updateOrCreate(
            [
                'teaching_session_id' => $data['teaching_session_id'],
                'student_id' => $data['student_id'],
            ],
            [
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
            ],
        );

        $notifications->createForStudent(
            $attendance->student,
            'attendance',
            'Attendance updated',
            "Attendance for {$session->title}: {$attendance->status}."
        );

        return back()->with('status', __('flash.attendance.saved'));
    }
}
