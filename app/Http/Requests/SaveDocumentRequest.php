<?php

namespace App\Http\Requests;

use App\Actions\SalesDocumentChoices;
use App\Models\TxnJob;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $model = SalesDocumentChoices::MODELS[$this->route('document_type')];
        if ($this->route('document')) {
            $model::forCompany($this->user()->company_id)->findOrFail($this->route('document'));
        }

        return true;
    }

    public function rules(): array
    {
        $type = $this->route('document_type');
        $companyId = $this->user()->company_id;
        $rules = [
            'client_id' => ['required', 'integer', Rule::exists('clients', 'id')->where('company_id', $companyId)],
            $type.'_date' => ['required', 'date_format:Y-m-d'],
            'status' => ['sometimes', Rule::in(SalesDocumentChoices::STATUSES[$type])],
            'notes' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*' => ['array:catalog_item_id,quotation_item_id,quantity'],
            'items.*.catalog_item_id' => ['nullable', 'integer', Rule::exists('items', 'id')->where('company_id', $companyId)],
            'items.*.quotation_item_id' => ['nullable', 'integer', 'distinct', Rule::exists('txn_history_items', 'id')->where('company_id', $companyId)->where('document_type', $type)->where('document_id', $this->route('document') ?? 0)],
            'items.*.quantity' => ['required', 'numeric', 'decimal:0,3', 'between:0.001,99999.999'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ];
        foreach (SalesDocumentChoices::FIELDS[$type] as $field) {
            $rules[$field] = match ($field) {
                'quotation_id', 'job_id' => ['nullable', 'integer', Rule::exists($field === 'job_id' ? 'txn_jobs' : 'txn_quotations', 'id')->where('company_id', $companyId)->where('client_id', $this->input('client_id'))],
                'terms' => ['nullable', 'string', 'max:10000'],
                default => ['nullable', 'date_format:Y-m-d', 'after_or_equal:'.$type.'_date'],
            };
        }
        if ($type === 'job' && $this->filled('start_date')) {
            $rules['completion_date'][] = 'after_or_equal:start_date';
        }

        return $rules;
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($this->route('document_type') !== 'invoice' || $validator->errors()->isNotEmpty() || ! $this->filled('job_id') || ! $this->filled('quotation_id')) {
                return;
            }
            $job = TxnJob::forCompany($this->user()->company_id)->find($this->input('job_id'));
            if ($job?->quotation_id && (int) $job->quotation_id !== (int) $this->input('quotation_id')) {
                $validator->errors()->add('job_id', 'The job belongs to a different quotation.');
            }
        }];
    }
}
