<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCatalogItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $item = $this->route('item');
        abort_if($item && (int) $item->company_id !== (int) $this->user()->company_id, 404);

        return $this->user()->hasPermission($item ? 'edit_catalog_items' : 'create_catalog_items');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['service', 'inventory'])],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'hsn' => ['required', 'string', 'max:20'],
            'gst_rate' => ['required_without:tax_id', 'nullable', 'numeric', 'decimal:0,4', 'between:0,100'],
            'tax_id' => ['nullable', 'integer', Rule::exists('taxes', 'id')->where('company_id', $this->user()->company_id)],
            'sku' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'rate' => ['required', 'numeric', 'decimal:0,2', 'between:0,99999999.99'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_image' => ['sometimes', 'boolean'],
        ];
    }
}
