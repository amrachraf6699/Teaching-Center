<?php

namespace Modules\Imports\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Exports\Support\TableExport;
use Modules\Imports\Models\ImportBatch;
use Modules\Imports\Services\ImportProcessor;
use Modules\Imports\Services\ImportTemplateRegistry;
use Modules\Imports\Support\TemplateExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportController extends Controller
{
    public function show(ImportBatch $batch): Response
    {
        return Inertia::render('Admin/Imports/Show', [
            'batch' => $this->batchPayload($batch) + [
                'errors' => $batch->errors ?? [],
            ],
        ]);
    }

    public function template(string $type, ?string $format, ImportTemplateRegistry $templates): StreamedResponse|BinaryFileResponse
    {
        abort_unless($templates->exists($type), 404);

        $format = $format ?: 'csv';
        abort_unless(in_array($format, ['csv', 'xlsx'], true), 404);

        $headers = $templates->headers($type);

        if ($format === 'xlsx') {
            return Excel::download(new TemplateExport($headers), "{$type}-import-template.xlsx");
        }

        return TableExport::csv("{$type}-import-template.csv", $headers, []);
    }

    public function store(Request $request, string $type, ImportProcessor $processor, ImportTemplateRegistry $templates): RedirectResponse
    {
        abort_unless($templates->exists($type), 404);

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:10240'],
        ]);

        $batch = $processor->process($validated['file'], $type, $request->user());

        return to_route('admin.imports.show', $batch)
            ->with(
                $batch->status === 'completed' ? 'success' : 'warning',
                $batch->status === 'completed'
                    ? 'Import completed successfully.'
                    : 'Import finished with errors. Review failed rows below.',
            );
    }

    private function batchPayload(ImportBatch $batch): array
    {
        return [
            'id' => $batch->id,
            'type' => $batch->type,
            'label' => str($batch->type)->replace('_', ' ')->headline()->toString(),
            'file_name' => $batch->file_name,
            'status' => $batch->status,
            'total_rows' => $batch->total_rows,
            'imported_rows' => $batch->imported_rows,
            'failed_rows' => $batch->failed_rows,
            'created_at' => $batch->created_at?->toDayDateTimeString(),
            'show_url' => route('admin.imports.show', $batch),
        ];
    }
}
