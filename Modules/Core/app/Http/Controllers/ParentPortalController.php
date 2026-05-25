<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Exams\Models\ExamResult;

class ParentPortalController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $children = $request->user()
            ->children()
            ->with(['groups', 'groups.sessions.attendanceRecords'])
            ->get()
            ->map(function ($child): array {
                $latestSession = $child->groups
                    ->flatMap(fn ($g) => $g->sessions)
                    ->sortByDesc('starts_at')
                    ->first();

                $lastAttendance = $latestSession
                    ? $latestSession->attendanceRecords->where('student_id', $child->id)->first()?->status
                    : null;

                $latestExam = ExamResult::query()
                    ->with('exam')
                    ->where('student_id', $child->id)
                    ->latest()
                    ->first();

                return [
                    'id' => $child->id,
                    'name' => $child->name,
                    'code' => $child->code,
                    'group_count' => $child->groups->count(),
                    'last_attendance' => $lastAttendance,
                    'latest_exam_percentage' => $latestExam?->percentage(),
                    'latest_exam_title' => $latestExam?->exam?->title,
                    'pdf_url' => route('parent.child.pdf', $child->id),
                ];
            });

        return Inertia::render('Parent/Dashboard', [
            'children' => $children,
        ]);
    }
}
