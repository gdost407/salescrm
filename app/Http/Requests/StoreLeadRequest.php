<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:30'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'deal_amount' => ['nullable', 'numeric', 'min:0'],
            'stage' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:255'],
            'source' => ['required', 'string', 'max:255'],
            'assigned_to' => ['nullable', 'integer'],
            'address' => ['nullable', 'string', 'max:65535'],
            'country' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'pincode' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:65535'],
        ];
    }
}
