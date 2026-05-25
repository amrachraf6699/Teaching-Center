<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class StudentAttendanceScannerController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Student/ScanAttendance');
    }
}
