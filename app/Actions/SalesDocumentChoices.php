<?php

namespace App\Actions;

use App\Models\TxnInvoice;
use App\Models\TxnJob;
use App\Models\TxnQuotation;
use Illuminate\Support\Collection;

class SalesDocumentChoices
{
    public const MODELS = ['quotation' => TxnQuotation::class, 'job' => TxnJob::class, 'invoice' => TxnInvoice::class];

    public const FIELDS = [
        'quotation' => ['valid_until', 'terms'],
        'job' => ['quotation_id', 'start_date', 'completion_date'],
        'invoice' => ['quotation_id', 'job_id', 'due_date', 'terms'],
    ];

    public const STATUSES = [
        'quotation' => ['draft', 'sent', 'accepted', 'rejected', 'expired', 'cancelled'],
        'job' => ['draft', 'pending', 'in_progress', 'completed', 'cancelled'],
        'invoice' => ['draft', 'issued', 'partially_paid', 'paid', 'overdue', 'cancelled'],
    ];

    public function handle(int $companyId): Collection
    {
        return collect(self::MODELS)->flatMap(fn (string $model, string $type) => $model::forCompany($companyId)
            ->orderByDesc('id')->get(['id', 'client_id', $type.'_no'])
            ->map(fn ($document) => [
                'value' => $type.':'.$document->id,
                'label' => ucfirst($type).' '.$document->{$type.'_no'},
                'client_id' => $document->client_id,
            ]));
    }
}
