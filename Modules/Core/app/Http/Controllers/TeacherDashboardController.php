<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;
use Modules\Academics\Models\GroupSession;
use Modules\Academics\Models\TeachingGroup;
use Modules\Exams\Models\Exam;
use Modules\People\Models\Student;

class TeacherDashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('core::admin.dashboard', [
            'studentCount' => Student::count(),
            'parentCount' => User::query()->where('role', 'parent')->count(),
            'groupCount' => TeachingGroup::count(),
            'sessionCount' => GroupSession::count(),
            'examCount' => Exam::count(),
        ]);
    }
}
