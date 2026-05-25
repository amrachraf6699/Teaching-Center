<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Exams\Models\ExamResult;
use Modules\People\Models\Student;
use Modules\Core\Settings\GeneralSettings;

class ParentChildPdfController extends Controller
{
    public function __invoke(Request $request, int $student): Response
    {
        $student = Student::query()
            ->where('id', $student)
            ->where('parent_id', $request->user()->id)
            ->with([
                'groups.sessions.attendanceRecords',
            ])
            ->firstOrFail();

        $examResults = ExamResult::query()
            ->with('exam.group')
            ->where('student_id', $student->id)
            ->latest()
            ->get();

        $schoolName = rescue(fn () => app(GeneralSettings::class)->name, config('app.name'), false);

        $initials = collect(explode(' ', $student->name))
            ->take(2)
            ->map(fn ($w) => strtoupper($w[0]))
            ->join('');

        $pdf = Pdf::loadView('pdf.student-profile', [
            'student' => $student,
            'examResults' => $examResults,
            'schoolName' => $schoolName,
            'initials' => $initials,
        ])->setPaper('a4');

        $filename = sprintf('%s-profile.pdf', str($student->name)->slug());

        return $pdf->download($filename);
    }
}
