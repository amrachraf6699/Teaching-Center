<?php

namespace Modules\Academics\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Academics\Models\Attendance;
use Modules\Academics\Models\GroupSession;

class StudentSessionAttendanceController extends Controller
{
    public function show(Request $request, GroupSession $session): Response|RedirectResponse
    {
        if (! $request->user()) {
            return redirect()->route('student.login', [
                'redirect' => $request->fullUrl(),
            ]);
        }

        abort_unless($request->user()->isStudent(), 403);

        $student = $request->user()->studentProfile;

        abort_unless($student, 403);

        $session->load(['group.students', 'attendanceRecords']);
        abort_unless($session->group?->students->contains('id', $student->id), 403);

        $attendance = $session->attendanceRecords->firstWhere('student_id', $student->id);

        return Inertia::render('Student/AttendanceScan', [
            'student' => [
                'name' => $student->name,
                'code' => $student->code,
            ],
            'session' => [
                'id' => $session->id,
                'title' => $session->title,
                'group' => $session->group?->name,
                'subject' => $session->group?->subject,
                'starts_at' => $session->starts_at?->toDayDateTimeString(),
                'ends_at' => $session->ends_at?->toDayDateTimeString(),
                'attendance' => $attendance ? [
                    'status' => $attendance->status,
                    'notes' => $attendance->notes,
                ] : null,
                'submit_url' => \URL::signedRoute('student.sessions.attendance.store', ['session' => $session]),
            ],
        ]);
    }

    public function store(Request $request, GroupSession $session): RedirectResponse
    {
        $student = $request->user()->studentProfile;

        abort_unless($student, 403);

        $session->load('group.students');
        abort_unless($session->group?->students->contains('id', $student->id), 403);

        $existingAttendance = Attendance::query()
            ->where('teaching_session_id', $session->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existingAttendance) {
            return back()->with('status', 'Attendance already recorded for this session.');
        }

        Attendance::query()->create([
            'teaching_session_id' => $session->id,
            'student_id' => $student->id,
            'status' => 'present',
            'notes' => 'Recorded from student QR scan.',
        ]);

        return back()->with('status', 'Attendance recorded successfully.');
    }
}
