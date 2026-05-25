<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ParentAttendanceController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $children = $request->user()
            ->children()
            ->with([
                'groups.sessions.attendanceRecords',
                'groups.sessions.group',
                'groups',
            ])
            ->get()
            ->map(fn ($child): array => [
                'id' => $child->id,
                'name' => $child->name,
                'code' => $child->code,
                'groups' => $child->groups->map(fn ($group): array => [
                    'id' => $group->id,
                    'name' => $group->name,
                    'subject' => $group->subject,
                    'level' => $group->level,
                    'sessions' => $group->sessions
                        ->sortByDesc('starts_at')
                        ->take(5)
                        ->map(fn ($session): array => [
                            'id' => $session->id,
                            'title' => $session->title,
                            'starts_at' => $session->starts_at?->toDayDateTimeString(),
                            'attendance' => $session->attendanceRecords
                                ->where('student_id', $child->id)
                                ->first()?->status,
                        ])
                        ->values(),
                ]),
            ]);

        return Inertia::render('Parent/Attendance', [
            'children' => $children,
        ]);
    }
}
