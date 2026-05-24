<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Exams\Models\ExamResult;
use Modules\Notifications\Models\ParentNotification;

class ParentPortalController extends Controller
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
                'phone' => $child->phone,
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
                'exam_results' => ExamResult::query()
                    ->with('exam.group')
                    ->where('student_id', $child->id)
                    ->latest()
                    ->limit(5)
                    ->get()
                    ->map(fn (ExamResult $result): array => [
                        'id' => $result->id,
                        'title' => $result->exam?->title,
                        'group' => $result->exam?->group?->name,
                        'schedule' => $result->exam?->start_at && $result->exam?->end_at
                            ? $result->exam->start_at->format('M j, Y g:i A').' - '.$result->exam->end_at->format('g:i A')
                            : '-',
                        'score' => $result->score,
                        'max_score' => $result->exam?->max_score,
                        'percentage' => $result->percentage(),
                    ]),
            ]);

        $notifications = ParentNotification::query()
            ->where('parent_id', $request->user()->id)
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (ParentNotification $notification): array => [
                'id' => $notification->id,
                'type' => $notification->type,
                'title' => $notification->title,
                'body' => $notification->body,
                'created_at' => $notification->created_at?->diffForHumans(),
            ]);

        return Inertia::render('Parent/Dashboard', [
            'children' => $children,
            'notifications' => $notifications,
        ]);
    }
}
