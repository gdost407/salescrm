<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        $record = $this->route('client');
        abort_if($record && (int) $record->company_id !== (int) $this->user()->company_id, 404);

        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['individual', 'business'])],
            'email' => ['nullable', 'email', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:30'],
            'is_active' => ['required', 'boolean'],
        ];
        foreach (['client_code', 'company_name', 'gst_no', 'pan_no', 'country', 'state', 'city', 'zip_code'] as $field) {
            $rules[$field] = ['nullable', 'string', 'max:255'];
        }
        foreach (['billing_address', 'shipping_address', 'notes'] as $field) {
            $rules[$field] = ['nullable', 'string', 'max:5000'];
        }

        return $rules;
    }
}
