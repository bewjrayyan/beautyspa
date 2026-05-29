<?php

namespace Modules\Report\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Report\Exports\ReportArrayExport;
use Modules\Report\Report;
use Modules\Support\Services\DompdfConfigurator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportService
{
    public function export(Report $report, Request $request, string $type, string $format): StreamedResponse|\Illuminate\Http\Response
    {
        $payload = ReportExportMapper::build($report, $request, $type);
        $filename = $this->filename($type, $format);

        return match ($format) {
            'csv' => $this->toCsv($payload, $filename),
            'xlsx' => $this->toExcel($payload, $filename),
            'pdf' => $this->toPdf($payload, $filename),
            default => abort(422, 'Unsupported export format.'),
        };
    }


    private function filename(string $type, string $format): string
    {
        $extension = $format === 'xlsx' ? 'xlsx' : $format;

        return Str::slug($type) . '-' . now()->format('Y-m-d-His') . '.' . $extension;
    }


    private function toCsv(array $payload, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($payload) {
            $handle = fopen('php://output', 'w');

            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, $payload['headings']);

            foreach ($payload['rows'] as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }


    private function toExcel(array $payload, string $filename)
    {
        return Excel::download(
            new ReportArrayExport($payload['headings'], $payload['rows']),
            $filename
        );
    }


    private function toPdf(array $payload, string $filename)
    {
        $html = view('report::admin.reports.export.pdf', [
            'title' => $payload['title'],
            'headings' => $payload['headings'],
            'rows' => $payload['rows'],
            'generatedAt' => now(),
        ])->render();

        $dompdf = new \Dompdf\Dompdf(DompdfConfigurator::createOptions());
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
