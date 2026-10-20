<?php

namespace App\Http\Controllers\Web\Quotation;

use App\Actions\ExportReportWorkbook;
use App\Actions\SalesDocumentChoices;
use App\Actions\SalesReport;
use App\Http\Controllers\Web\DocumentController;
use App\Models\Client;
use App\Models\TxnQuotation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class QuotationController extends DocumentController
{
    protected string $type = 'quotation';

    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $records = TxnQuotation::forCompany($request->user()->company_id)
            ->with('client')
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->where('quotation_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->where('quotation_date', '<=', $date))
            ->when(($filters['search'] ?? '') !== '', function ($query) use ($filters, $request): void {
                $search = $filters['search'];
                $query->where(function ($query) use ($search, $request): void {
                    $query->where('quotation_no', 'like', '%'.$search.'%')
                        ->orWhere('title', 'like', '%'.$search.'%')
                        ->orWhere('status', 'like', '%'.$search.'%')
                        ->orWhereHas('client', function ($clientQuery) use ($search, $request): void {
                            $clientQuery->where('company_id', $request->user()->company_id)
                                ->where(function ($clientQuery) use ($search): void {
                                    $clientQuery->where('name', 'like', '%'.$search.'%')
                                        ->orWhere('company_name', 'like', '%'.$search.'%')
                                        ->orWhere('mobile', 'like', '%'.$search.'%')
                                        ->orWhere('email', 'like', '%'.$search.'%');
                                });
                        });
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('web.quotation.index', [
            'records' => $records,
            'filters' => $filters,
            'statuses' => SalesDocumentChoices::STATUSES['quotation'],
            'type' => $this->type,
            'resource' => 'quotations',
        ]);
    }

    public function export(Request $request, ExportReportWorkbook $export, SalesReport $report): BinaryFileResponse
    {
        $filters = $this->filters($request);
        $columns = [
            'quotation_no' => 'Number',
            'title' => 'Title',
            'client.name' => 'Client',
            'quotation_date' => 'Date',
            'valid_until' => 'Valid until',
            'status' => 'Status',
            'total_amount' => 'Total (INR)',
        ];
        $records = TxnQuotation::forCompany($request->user()->company_id)
            ->with('client')
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->where('quotation_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->where('quotation_date', '<=', $date))
            ->when(($filters['search'] ?? '') !== '', function ($query) use ($filters, $request): void {
                $search = $filters['search'];
                $query->where(function ($query) use ($search, $request): void {
                    $query->where('quotation_no', 'like', '%'.$search.'%')
                        ->orWhere('title', 'like', '%'.$search.'%')
                        ->orWhere('status', 'like', '%'.$search.'%')
                        ->orWhereHas('client', function ($clientQuery) use ($search, $request): void {
                            $clientQuery->where('company_id', $request->user()->company_id)
                                ->where(function ($clientQuery) use ($search): void {
                                    $clientQuery->where('name', 'like', '%'.$search.'%')
                                        ->orWhere('company_name', 'like', '%'.$search.'%')
                                        ->orWhere('mobile', 'like', '%'.$search.'%')
                                        ->orWhere('email', 'like', '%'.$search.'%');
                                });
                        });
                });
            })
            ->orderBy('id')
            ->lazyById(250)
            ->map(fn (TxnQuotation $record) => $report->row($record, $columns));
        $path = $export->handle($columns, $records);

        return response()->download($path, 'quotations-'.now()->format('Y-m-d-His').'.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ])->deleteFileAfterSend(true);
    }

    /** @return array<string, mixed> */
    private function filters(Request $request): array
    {
        $filters = $request->validate([
            'date_preset' => ['nullable', Rule::in(['today', 'this_week', 'last_7_days', 'this_month', 'this_year', 'custom'])],
            'date_from' => ['nullable', 'date_format:Y-m-d', 'before:9999-12-31'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'before:9999-12-31'],
            'status' => ['nullable', Rule::in(SalesDocumentChoices::STATUSES['quotation'])],
            'search' => ['nullable', 'string', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);
        $filters['search'] = trim($filters['search'] ?? '');
        if (($filters['date_preset'] ?? null) === 'custom' && ! empty($filters['date_from']) && ! empty($filters['date_to'])) {
            validator($filters, ['date_to' => ['after_or_equal:date_from']])->validate();
        }

        $today = today();
        [$from, $to] = match ($filters['date_preset'] ?? null) {
            'today' => [$today, $today],
            'this_week' => [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()],
            'last_7_days' => [$today->copy()->subDays(6), $today],
            'this_month' => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
            'this_year' => [$today->copy()->startOfYear(), $today->copy()->endOfYear()],
            'custom' => [$filters['date_from'] ?? null, $filters['date_to'] ?? null],
            default => [null, null],
        };
        $filters['date_from'] = $from instanceof Carbon ? $from->toDateString() : $from;
        $filters['date_to'] = $to instanceof Carbon ? $to->toDateString() : $to;

        return $filters;
    }

    public function searchClients(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $query = Client::query()
            ->where('company_id', $request->user()->company_id)
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if (filled($validated['q'] ?? null)) {
            $search = '%'.$validated['q'].'%';
            $query->where(function ($clientQuery) use ($search): void {
                $clientQuery->where('name', 'like', $search)
                    ->orWhere('mobile', 'like', $search)
                    ->orWhere('email', 'like', $search);
            });
        }

        return response()->json([
            'data' => $query->limit(20)->get([
                'id', 'name', 'company_name', 'email', 'mobile', 'billing_address', 'shipping_address', 'country', 'state', 'city', 'zip_code',
            ]),
        ]);
    }
}
