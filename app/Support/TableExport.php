<?php

namespace App\Support;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TableExport
{
    public static function csv(string $filename, array $headers, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public static function pdf(string $filename, string $title, array $headers, array $rows, string $orientation = 'portrait'): Response
    {
        return Pdf::loadView('exports.table', [
            'title' => $title,
            'headers' => $headers,
            'rows' => $rows,
        ])->setPaper('a4', $orientation)->download($filename);
    }
}
