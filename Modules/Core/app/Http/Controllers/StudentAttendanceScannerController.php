<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class StudentAttendanceScannerController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        return redirect()->route('student.home');
    }
}
