<?php

namespace App\Actions;

use App\Models\Client;
use App\Models\Company;
use App\Models\Item;
use App\Models\User;
use Brick\Math\BigDecimal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaveSalesDocument
{
    public function __construct(private CalculateQuotationLine $calculateLine) {}

    /** @param array{client_id: int|string, items: array<int, array{catalog_item_id?: int|string|null, quotation_item_id?: int|string|null, quantity: int|float|string}>, quotation_date?: string, job_date?: string, invoice_date?: string, status?: string, notes?: ?string, terms?: ?string, valid_until?: ?string, start_date?: ?string, completion_date?: ?string, due_date?: ?string, quotation_id?: int|string|null, job_id?: int|string|null} $data */
    public function handle(User $user, array $data, string $type, ?Model $document = null): Model
    {
        $model = SalesDocumentChoices::MODELS[$type];

        return DB::transaction(function () use ($user, $data, $type, $model, $document): Model {
            if ($document) {
                $document = $model::query()->where('company_id', $user->company_id)->lockForUpdate()->findOrFail($document->id);
            }

            $client = Client::query()->where('company_id', $user->company_id)->findOrFail($data['client_id']);
            foreach (['quotation_id' => 'quotation', 'job_id' => 'job'] as $field => $parentType) {
                if (! in_array($field, SalesDocumentChoices::FIELDS[$type], true) || empty($data[$field])) {
                    continue;
                }
                $parentModel = SalesDocumentChoices::MODELS[$parentType];
                $parent = $parentModel::forCompany($user->company_id)->lockForUpdate()->find($data[$field]);
                if (! $parent || (int) $parent->client_id !== (int) $client->id) {
                    throw ValidationException::withMessages([$field => 'Select a document belonging to this client.']);
                }
                if ($parentType === 'job' && $parent->quotation_id && ! empty($data['quotation_id']) && (int) $parent->quotation_id !== (int) $data['quotation_id']) {
                    throw ValidationException::withMessages([$field => 'The job belongs to a different quotation.']);
                }
            }
            if ($type === 'job' && $document && (int) $document->quotation_id !== (int) ($data['quotation_id'] ?? null) && $document->invoices()->exists()) {
                throw ValidationException::withMessages(['quotation_id' => 'The quotation cannot change while invoices are linked to this job.']);
            }
            $catalog = Item::query()->with('tax')->where('company_id', $user->company_id)
                ->whereIn('id', array_column($data['items'], 'catalog_item_id'))->get()->keyBy('id');
            $oldItems = $document?->items()->get()->keyBy('id') ?? collect();
            $totals = ['subtotal' => BigDecimal::zero(), 'tax_total' => BigDecimal::zero(), 'total' => BigDecimal::zero()];
            $lines = [];

            foreach ($data['items'] as $index => $row) {
                $oldItem = $oldItems->get($row['quotation_item_id'] ?? null);
                if (! empty($row['quotation_item_id']) && ! $oldItem) {
                    throw ValidationException::withMessages(['items' => 'This document has changed. Reload it before saving.']);
                }
                $item = $catalog->get($row['catalog_item_id'] ?? null);
                $keepSnapshot = $oldItem && (int) $oldItem->item_id === (int) ($row['catalog_item_id'] ?? null);

                if (! $keepSnapshot && (! $item || ! $item->is_active)) {
                    throw ValidationException::withMessages(["items.$index.catalog_item_id" => 'Select an available item from your company.']);
                }

                if ($keepSnapshot) {
                    $line = $oldItem->only(['item_id', 'item_type', 'item_name', 'description', 'hsn_sac', 'sku', 'rate', 'tax_id', 'tax_rate', 'tax_type']);
                } else {
                    if ($item->tax && (int) $item->tax->company_id !== (int) $user->company_id) {
                        throw ValidationException::withMessages(["items.$index.catalog_item_id" => 'The item tax does not belong to your company.']);
                    }
                    $line = $item->only(['description', 'hsn_sac', 'sku', 'rate', 'tax_id', 'tax_type']);
                    $line += ['item_id' => $item->id, 'item_type' => $item->type, 'item_name' => $item->name, 'tax_rate' => $item->tax?->rate ?? '0.0000'];
                }
                $line['company_id'] = $user->company_id;
                $line['qty'] = (string) $row['quantity'];
                $line['sort_order'] = count($lines);
                $amounts = $this->calculateLine->handle($line['tax_type'] === 'inclusive' ? 'inventory' : 'service', $line['rate'], $line['qty'], $line['tax_rate']);
                $line += ['taxable_amount' => $amounts['subtotal'], 'tax_amount' => $amounts['tax_total'], 'total_amount' => $amounts['total']];
                $lines[] = $line;

                foreach ($totals as $key => $amount) {
                    $totals[$key] = $amount->plus($amounts[$key]);
                }
            }

            if ($document && BigDecimal::of($document->discount_amount)->isGreaterThan(0)) {
                throw ValidationException::withMessages(['items' => 'This document has a discount. Editing discounted documents is not supported by this form.']);
            }
            $attributes = [
                'client_id' => $client->id,
                $type.'_date' => $data[$type.'_date'],
                'status' => $data['status'] ?? $document?->status ?? 'draft',
                'notes' => $data['notes'] ?? null,
            ];

            foreach (SalesDocumentChoices::FIELDS[$type] as $field) {
                $attributes[$field] = $data[$field] ?? null;
            }
            if ($document && (int) $document->client_id !== (int) $client->id) {
                $relations = ['payments', 'ledgerEntries'];
                if ($type === 'quotation') {
                    $relations = array_merge($relations, ['jobs', 'invoices']);
                } elseif ($type === 'job') {
                    $relations[] = 'invoices';
                }
                foreach ($relations as $relation) {
                    if ($document->$relation()->exists()) {
                        throw ValidationException::withMessages(['client_id' => 'The client cannot change while linked records exist.']);
                    }
                }
            }

            $columns = ['subtotal' => 'subtotal', 'tax_total' => 'tax_amount', 'total' => 'total_amount'];
            foreach ($totals as $key => $amount) {
                if ($amount->isGreaterThan('9999999999999.99')) {
                    throw ValidationException::withMessages(['items' => 'The document amount exceeds the supported limit.']);
                }
                $attributes[$columns[$key]] = (string) $amount->toScale(2);
            }

            if ($type === 'invoice') {
                $balance = BigDecimal::of($attributes['total_amount'])->minus($document?->paid_amount ?? '0.00');
                if ($balance->isLessThan(0)) {
                    throw ValidationException::withMessages(['items' => 'The total cannot be below the stored paid amount.']);
                }
                $attributes['balance_amount'] = (string) $balance->toScale(2);
            }

            if ($document) {
                $document->update($attributes);
                $document->items()->delete();
            } else {
                Company::query()->lockForUpdate()->findOrFail($user->company_id);
                $sequence = ((int) $model::forCompany($user->company_id)->max('id')) + 1;
                do {
                    $number = ['quotation' => 'QT-', 'job' => 'JB-', 'invoice' => 'INV-'][$type].str_pad((string) $sequence++, 6, '0', STR_PAD_LEFT);
                } while ($model::forCompany($user->company_id)->where($type.'_no', $number)->exists());
                $document = $model::create($attributes + ['company_id' => $user->company_id, 'created_by' => $user->id, $type.'_no' => $number]);
            }

            $document->items()->createMany($lines);

            return $document;
        });
    }
}
