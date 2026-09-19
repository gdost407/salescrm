<?php

namespace App\Http\Requests;

use App\Actions\SalesDocumentChoices;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SavePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $record = $this->route('payment');
        abort_if($record && (int) $record->company_id !== (int) $this->user()->company_id, 404);

        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('document') && (is_string($this->input('document')) || $this->input('document') === null)) {
            [$type, $id] = array_pad(explode(':', $this->input('document') ?? '', 2), 2, null);
            $this->merge(['document_type' => $type ?: null, 'document_id' => $id]);
        }
    }

    public function rules(): array
    {
        return [
            'document' => ['nullable', 'string', 'max:100'],
            'client_id' => ['required', 'nullable', 'integer', Rule::exists('clients', 'id')->where('company_id', $this->user()->company_id)],
            'document_type' => ['required', 'required_with:document_id', Rule::in(array_keys(SalesDocumentChoices::MODELS))],
            'document_id' => ['required', 'required_with:document_type', 'integer', 'min:1'],
            'payment_date' => ['required', 'date_format:Y-m-d'],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'between:0.01,9999999999999.99'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'payment_no' => ['required', 'string', 'max:255', Rule::unique('txn_payments', 'payment_no')->where('company_id', $this->user()->company_id)->ignore($this->route('payment')?->id)],
            'payment_mode' => ['required', Rule::in(['cash', 'bank', 'upi', 'card', 'cheque', 'other'])],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty() || ! $this->filled('document_type')) {
                return;
            }
            $model = SalesDocumentChoices::MODELS[$this->input('document_type')];
            $query = $model::forCompany($this->user()->company_id)->whereKey($this->input('document_id'));
            if ($this->filled('client_id')) {
                $query->where('client_id', $this->input('client_id'));
            }
            if (! $query->exists()) {
                $validator->errors()->add('document_id', 'Select a document belonging to this company and client.');
            }
        }];
    }
}
