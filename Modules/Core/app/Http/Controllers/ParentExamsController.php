<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Exams\Models\ExamResult;

class ParentExamsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $children = $request->user()
            ->children()
            ->get()
            ->map(fn ($child): array => [
                'id' => $child->id,
                'name' => $child->name,
                'code' => $child->code,
                'exam_results' => ExamResult::query()
                    ->with('exam.group')
                    ->where('student_id', $child->id)
                    ->latest()
                    ->limit(10)
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

        return Inertia::render('Parent/Exams', [
            'children' => $children,
        ]);
    }
}
