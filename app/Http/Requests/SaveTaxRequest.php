<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveTaxRequest extends FormRequest
{
    public function authorize(): bool
    {
        $record = $this->route('tax');
        abort_if($record && (int) $record->company_id !== (int) $this->user()->company_id, 404);

        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'rate' => ['required', 'numeric', 'decimal:0,4', 'between:0,100'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
