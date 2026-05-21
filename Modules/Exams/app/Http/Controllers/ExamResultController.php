<?php

namespace Modules\Exams\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Exams\Models\Exam;
use Modules\Exams\Models\ExamResult;
use Modules\Notifications\Services\ParentNotificationService;

class ExamResultController extends Controller
{
    public function store(Request $request, Exam $exam, ParentNotificationService $notifications): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'score' => ['required', 'numeric', 'min:0', 'max:'.$exam->max_score],
            'notes' => ['nullable', 'string'],
        ]);

        $exam->load('group.students');
        abort_unless($exam->group->students->contains('id', (int) $data['student_id']), 422);

        $result = ExamResult::updateOrCreate(
            [
                'exam_id' => $exam->id,
                'student_id' => $data['student_id'],
            ],
            [
                'score' => $data['score'],
                'notes' => $data['notes'] ?? null,
            ],
        );

        $notifications->createForStudent(
            $result->student,
            'exam_result',
            'Exam result posted',
            "{$exam->title}: {$result->score} / {$exam->max_score}."
        );

        return back()->with('status', 'Exam result saved.');
    }
}
