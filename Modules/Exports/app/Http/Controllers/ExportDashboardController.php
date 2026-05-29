<?php

namespace Modules\Exports\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ExportDashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Admin/Exports/Index', [
            'exports' => [
                ['label' => 'Parents', 'csv_url' => route('admin.parents.export', 'csv'), 'pdf_url' => route('admin.parents.export', 'pdf')],
                ['label' => 'Students', 'csv_url' => route('admin.students.export', 'csv'), 'pdf_url' => route('admin.students.export', 'pdf')],
                ['label' => 'Groups', 'csv_url' => route('admin.groups.export', 'csv'), 'pdf_url' => route('admin.groups.export', 'pdf')],
                ['label' => 'Timetables', 'csv_url' => route('admin.timetables.export', 'csv'), 'pdf_url' => route('admin.timetables.export', 'pdf')],
                ['label' => 'Sessions', 'csv_url' => route('admin.sessions.export', 'csv'), 'pdf_url' => route('admin.sessions.export', 'pdf')],
                ['label' => 'Exams', 'csv_url' => route('admin.exams.export', 'csv'), 'pdf_url' => route('admin.exams.export', 'pdf')],
            ],
        ]);
    }
}
