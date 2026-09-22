<?php

namespace App\Http\Requests;

use App\Actions\SalesDocumentChoices;
use App\Actions\SalesReport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $type = $this->route('type');
        abort_unless(isset(SalesReport::MODULES[$type]), 404);

        return $this->user()->hasPermission(SalesReport::MODULES[$type]['permission']);
    }

    public function rules(): array
    {
        $rules = [
            'q' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date_format:Y-m-d', 'before:9999-12-31'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'before:9999-12-31'],
            'per_page' => ['sometimes', Rule::in([25, 50, 100, 250])],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
        if ($this->filled('date_from')) {
            $rules['date_to'][] = 'after_or_equal:date_from';
        }
        if ($this->route('type') === 'lead') {
            foreach (['status', 'stage', 'source', 'priority'] as $field) {
                $rules[$field] = ['nullable', 'string', 'max:255'];
            }
            $rules['assigned_to'] = ['nullable', 'integer', Rule::exists('users', 'id')->where('company_id', $this->user()->company_id)];
        } else {
            $rules['client_id'] = ['nullable', 'integer', Rule::exists('clients', 'id')->where('company_id', $this->user()->company_id)];
            if ($this->route('type') === 'ledger') {
                $rules += [
                    'ledger_type' => ['nullable', Rule::in(['client', 'company'])],
                    'entry_type' => ['nullable', Rule::in(['debit', 'credit'])],
                    'document_type' => ['nullable', Rule::in(['quotation', 'job', 'invoice'])],
                    'transaction_type' => ['nullable', 'string', 'max:255'],
                ];
            } else {
                $rules['status'] = ['nullable', Rule::in(SalesDocumentChoices::STATUSES[$this->route('type')])];
            }
        }

        return $rules;
    }
}
