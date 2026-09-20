<?php

namespace App\Actions;

use App\Models\Client;
use App\Models\Lead;
use App\Models\TxnInvoice;
use App\Models\TxnJob;
use App\Models\TxnLedger;
use App\Models\TxnQuotation;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class SalesReport
{
    public const MODULES = [
        'lead' => ['title' => 'Lead', 'permission' => 'view_own_leads', 'model' => Lead::class, 'date' => 'created_at'],
        'quotation' => ['title' => 'Quotation', 'permission' => 'view_quotations', 'model' => TxnQuotation::class, 'date' => 'quotation_date'],
        'job' => ['title' => 'Job', 'permission' => 'view_jobs', 'model' => TxnJob::class, 'date' => 'job_date'],
        'invoice' => ['title' => 'Invoice', 'permission' => 'view_invoices', 'model' => TxnInvoice::class, 'date' => 'invoice_date'],
        'ledger' => ['title' => 'Ledger', 'permission' => 'view_ledger', 'model' => TxnLedger::class, 'date' => 'transaction_date'],
    ];

    public const NUMERIC = ['deal_amount', 'subtotal', 'discount_amount', 'tax_amount', 'total_amount', 'paid_amount', 'balance_amount', 'amount', 'items_count'];

    /** @return array<string, string> */
    public function columns(string $type): array
    {
        $audit = ['creator.name' => 'Created by', 'created_at' => 'Created at', 'updated_at' => 'Updated at'];
        if ($type === 'lead') {
            return [
                'id' => 'Lead ID', 'name' => 'Name', 'company_name' => 'Company', 'email' => 'Email', 'mobile' => 'Mobile',
                'alternate_mobile' => 'Alternate mobile', 'job_title' => 'Job title', 'status' => 'Status', 'stage' => 'Stage',
                'source' => 'Source', 'priority' => 'Priority', 'deal_amount' => 'Deal amount (INR)', 'assignee.name' => 'Assigned to',
                'client_id' => 'Client ID', 'converted_at' => 'Converted at', 'last_contacted_at' => 'Last contacted',
                'last_activity_at' => 'Last activity', 'address' => 'Address', 'city' => 'City', 'state' => 'State',
                'country' => 'Country', 'pincode' => 'Postal code', 'description' => 'Description', 'notes' => 'Notes',
            ] + $audit;
        }
        $client = [
            'client.name' => 'Client', 'client.client_code' => 'Client code', 'client.company_name' => 'Client company',
            'client.email' => 'Email', 'client.mobile' => 'Mobile', 'client.gst_no' => 'GSTIN',
            'client.billing_address' => 'Billing address', 'client.city' => 'City', 'client.state' => 'State',
        ];
        if ($type === 'ledger') {
            return ['id' => 'Entry ID', 'transaction_date' => 'Date', 'ledger_type' => 'Ledger type'] + $client + [
                'entry_type' => 'Debit / Credit', 'transaction_type' => 'Transaction type', 'document_type' => 'Document type',
                'document_id' => 'Document ID', 'amount' => 'Amount (INR)', 'reference_no' => 'Reference', 'description' => 'Description',
            ] + $audit;
        }
        $extra = match ($type) {
            'quotation' => ['valid_until' => 'Valid until'],
            'job' => ['quotation.quotation_no' => 'Quotation', 'start_date' => 'Start date', 'completion_date' => 'Completion date'],
            'invoice' => ['quotation.quotation_no' => 'Quotation', 'job.job_no' => 'Job', 'due_date' => 'Due date'],
        };

        return [$type.'_no' => 'Number', $type.'_date' => 'Date', 'status' => 'Status'] + $client + $extra + [
            'items_count' => 'Item rows', 'subtotal' => 'Subtotal (INR)', 'discount_amount' => 'Discount (INR)',
            'tax_amount' => 'GST / Tax (INR)', 'total_amount' => 'Total (INR)',
        ] + ($type === 'invoice' ? ['paid_amount' => 'Stored paid (INR)', 'balance_amount' => 'Stored balance (INR)'] : [])
            + ['notes' => 'Notes'] + ($type !== 'job' ? ['terms' => 'Terms'] : []) + $audit;
    }

    /** @param array<string, mixed> $filters */
    public function query(User $user, string $type, array $filters = []): Builder
    {
        $model = self::MODULES[$type]['model'];
        $query = $type === 'lead' ? Lead::visibleTo($user) : $model::forCompany($user->company_id);
        $relations = ['creator' => fn ($query) => $query->where('company_id', $user->company_id)];
        if ($type === 'lead') {
            $relations['assignee'] = fn ($query) => $query->where('company_id', $user->company_id);
        } else {
            $relations['client'] = fn ($query) => $query->where('company_id', $user->company_id);
            if ($type !== 'ledger') {
                $query->withCount(['items' => fn ($query) => $query->where('company_id', $user->company_id)]);
                foreach ($type === 'invoice' ? ['quotation', 'job'] : ($type === 'job' ? ['quotation'] : []) as $relation) {
                    $relations[$relation] = fn ($query) => $query->where('company_id', $user->company_id);
                }
            }
        }
        $query->with($relations);
        $date = self::MODULES[$type]['date'];
        if (! empty($filters['date_from'])) {
            $query->where($date, '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->where($date, '<', Carbon::parse($filters['date_to'])->addDay()->toDateString());
        }
        $fields = match ($type) {
            'lead' => ['status', 'stage', 'source', 'assigned_to', 'priority'],
            'ledger' => ['client_id', 'ledger_type', 'entry_type', 'transaction_type', 'document_type'],
            default => ['client_id', 'status'],
        };
        foreach ($fields as $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $query->where($field, $filters[$field]);
            }
        }
        if (! empty($filters['q'])) {
            $term = '%'.$filters['q'].'%';
            $query->where(function (Builder $query) use ($type, $term, $user): void {
                $fields = match ($type) {
                    'lead' => ['name', 'email', 'mobile', 'company_name'],
                    'ledger' => ['reference_no', 'description'],
                    default => [$type.'_no', 'notes'],
                };
                foreach ($fields as $field) {
                    $query->orWhere($field, 'like', $term);
                }
                if ($type !== 'lead') {
                    $query->orWhereHas('client', fn (Builder $client) => $client->where('company_id', $user->company_id)
                        ->where(fn (Builder $client) => $client->where('name', 'like', $term)->orWhere('company_name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('mobile', 'like', $term)));
                }
            });
        }

        return $query;
    }

    /** @return array<string, array<string|int, string>> */
    public function options(User $user, string $type): array
    {
        if ($type === 'lead') {
            $options = [];
            foreach (['status', 'stage', 'source', 'priority'] as $field) {
                $options[$field] = Lead::visibleTo($user)->whereNotNull($field)->distinct()->orderBy($field)->pluck($field, $field)->all();
            }
            $options['assigned_to'] = User::where('company_id', $user->company_id)
                ->when(! $user->hasPermission('view_all_leads'), fn (Builder $query) => $query->whereKey($user->id))
                ->orderBy('name')->pluck('name', 'id')->all();

            return $options;
        }
        $options = ['client_id' => Client::where('company_id', $user->company_id)->orderBy('name')->pluck('name', 'id')->all()];
        if ($type === 'ledger') {
            return $options + [
                'ledger_type' => ['client' => 'Client', 'company' => 'Company'],
                'entry_type' => ['debit' => 'Debit', 'credit' => 'Credit'],
                'document_type' => ['quotation' => 'Quotation', 'job' => 'Job', 'invoice' => 'Invoice'],
                'transaction_type' => TxnLedger::forCompany($user->company_id)->distinct()->orderBy('transaction_type')->pluck('transaction_type', 'transaction_type')->all(),
            ];
        }

        return $options + ['status' => array_combine(SalesDocumentChoices::STATUSES[$type], SalesDocumentChoices::STATUSES[$type])];
    }

    /** @param array<string, string> $columns
     * @return array<string, string>
     */
    public function row(Model $record, array $columns): array
    {
        $row = [];
        foreach ($columns as $field => $label) {
            $value = data_get($record, $field);
            $row[$field] = $value instanceof DateTimeInterface
                ? $value->format(in_array($field, ['created_at', 'updated_at', 'converted_at', 'last_contacted_at', 'last_activity_at'], true) ? 'Y-m-d H:i:s' : 'Y-m-d')
                : (string) ($value ?? '');
        }

        return $row;
    }
}
