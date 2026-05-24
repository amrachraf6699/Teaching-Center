<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudentDashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $request->user()->load([
            'studentProfile.groups',
            'studentProfile.attendanceRecords.session.group',
        ]);

        $student = $request->user()->studentProfile;

        return Inertia::render('Student/Dashboard', [
            'student' => [
                'id' => $student?->id,
                'name' => $student?->name,
                'code' => $student?->code,
                'groups' => $student?->groups->map(fn ($group): array => [
                    'id' => $group->id,
                    'name' => $group->name,
                    'subject' => $group->subject,
                ])->values()->all() ?? [],
                'attendance' => $student?->attendanceRecords
                    ->sortByDesc('created_at')
                    ->take(10)
                    ->map(fn ($attendance): array => [
                        'id' => $attendance->id,
                        'status' => $attendance->status,
                        'notes' => $attendance->notes,
                        'session' => $attendance->session?->title,
                        'group' => $attendance->session?->group?->name,
                        'starts_at' => $attendance->session?->starts_at?->toDayDateTimeString(),
                    ])->values()->all() ?? [],
            ],
        ]);
    }
}
