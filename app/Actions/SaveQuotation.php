<?php

namespace App\Actions;

use App\Models\Client;
use App\Models\Company;
use App\Models\Item;
use App\Models\TxnQuotation;
use App\Models\User;
use Brick\Math\BigDecimal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaveQuotation
{
    public function __construct(private CalculateQuotationLine $calculateLine) {}

    /** @param array{client_id: int|string, quotation_date: string, valid_until?: ?string, notes?: ?string, terms?: ?string, items: array<int, array{catalog_item_id: int|string, quotation_item_id?: int|string|null, quantity: int|float|string}>} $data */
    public function handle(User $user, array $data, ?TxnQuotation $quotation = null): TxnQuotation
    {
        return DB::transaction(function () use ($user, $data, $quotation): TxnQuotation {
            if ($quotation) {
                $quotation = TxnQuotation::query()->where('company_id', $user->company_id)->lockForUpdate()->findOrFail($quotation->id);
            }

            $client = Client::query()->where('company_id', $user->company_id)->findOrFail($data['client_id']);
            $catalog = Item::query()->with('tax')->where('company_id', $user->company_id)
                ->whereIn('id', array_column($data['items'], 'catalog_item_id'))->get()->keyBy('id');
            $oldItems = $quotation?->items()->get()->keyBy('id') ?? collect();
            $totals = ['subtotal' => BigDecimal::zero(), 'tax_total' => BigDecimal::zero(), 'total' => BigDecimal::zero()];
            $lines = [];

            foreach ($data['items'] as $index => $row) {
                $oldItem = $oldItems->get($row['quotation_item_id'] ?? null);
                if (! empty($row['quotation_item_id']) && ! $oldItem) {
                    throw ValidationException::withMessages(['items' => 'This quotation has changed. Reload it before saving.']);
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

            if ($quotation && BigDecimal::of($quotation->discount_amount)->isGreaterThan(0)) {
                throw ValidationException::withMessages(['items' => 'This quotation has a discount. Editing discounted quotations is not supported by this form.']);
            }
            $attributes = [
                'client_id' => $client->id,
                'quotation_date' => $data['quotation_date'],
                'valid_until' => $data['valid_until'] ?? null,
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? null,
            ];

            $columns = ['subtotal' => 'subtotal', 'tax_total' => 'tax_amount', 'total' => 'total_amount'];
            foreach ($totals as $key => $amount) {
                if ($amount->isGreaterThan('9999999999999.99')) {
                    throw ValidationException::withMessages(['items' => 'The quotation amount exceeds the supported limit.']);
                }
                $attributes[$columns[$key]] = (string) $amount->toScale(2);
            }

            if ($quotation) {
                $quotation->update($attributes);
                $quotation->items()->delete();
            } else {
                Company::query()->lockForUpdate()->findOrFail($user->company_id);
                $sequence = ((int) TxnQuotation::forCompany($user->company_id)->max('id')) + 1;
                do {
                    $number = 'QT-'.str_pad((string) $sequence++, 6, '0', STR_PAD_LEFT);
                } while (TxnQuotation::forCompany($user->company_id)->where('quotation_no', $number)->exists());
                $quotation = TxnQuotation::create($attributes + ['company_id' => $user->company_id, 'created_by' => $user->id, 'quotation_no' => $number]);
            }

            $quotation->items()->createMany($lines);

            return $quotation;
        });
    }
}
