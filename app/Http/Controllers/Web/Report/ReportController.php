<?php

namespace App\Http\Controllers\Web\Report;

use App\Actions\ExportReportWorkbook;
use App\Actions\SalesReport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportRequest;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function index(ReportRequest $request, SalesReport $report): View
    {
        $type = $request->route('type');
        $filters = $request->validated();
        $columns = $report->columns($type);
        $records = $report->query($request->user(), $type, $filters)->orderByDesc('id')
            ->paginate((int) ($filters['per_page'] ?? 50))->withQueryString();
        $rows = $records->getCollection()->map(fn ($record) => $report->row($record, $columns));
        $options = $report->options($request->user(), $type);
        $title = SalesReport::MODULES[$type]['title'].' report';

        return view('web.report.index', compact('type', 'title', 'filters', 'columns', 'records', 'rows', 'options'));
    }

    public function export(ReportRequest $request, SalesReport $report, ExportReportWorkbook $export): BinaryFileResponse
    {
        $type = $request->route('type');
        $columns = $report->columns($type);
        $records = $report->query($request->user(), $type, $request->validated())->lazyById(250);
        $rows = $records->map(fn ($record) => $report->row($record, $columns));
        $path = $export->handle($columns, $rows);

        return response()->download($path, $type.'-report-'.now()->format('Y-m-d-His').'.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ])->deleteFileAfterSend(true);
    }
}
