<?php

namespace App\Http\Requests;

use App\Models\Item;
use App\Models\TxnHistoryItem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveQuotationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $quotation = $this->route('quotation');
        abort_if($quotation && (int) $quotation->company_id !== (int) $this->user()->company_id, 404);

        return $this->user()->hasPermission($quotation ? 'edit_quotations' : 'create_quotations');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'integer', Rule::exists('clients', 'id')->where('company_id', $this->user()->company_id)],
            'quotation_date' => ['required', 'date_format:Y-m-d'],
            'valid_until' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:quotation_date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'terms' => ['nullable', 'string', 'max:10000'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*' => ['required', 'array:catalog_item_id,quotation_item_id,quantity'],
            'items.*.catalog_item_id' => ['nullable', 'integer', Rule::exists(Item::class, 'id')->where('company_id', $this->user()->company_id)],
            'items.*.quotation_item_id' => ['nullable', 'integer', 'distinct', Rule::exists(TxnHistoryItem::class, 'id')->where('document_type', 'quotation')->where('document_id', $this->route('quotation')?->id ?? 0)->where('company_id', $this->user()->company_id)],
            'items.*.quantity' => ['required', 'numeric', 'decimal:0,3', 'between:0.001,99999.999'],
        ];
    }
}
