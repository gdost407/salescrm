<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitSubscriptionPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->is_active && $user->user_type === 'owner' && $user->company_id
            && (string) $user->company_id === $this->query('company')
            && (string) $user->id === $this->query('user');
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('utr'))) {
            $this->merge(['utr' => strtoupper(trim($this->input('utr')))]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'utr' => ['required', 'string', 'regex:/^[A-Z0-9]{6,64}$/', Rule::unique('subscription_payments', 'transaction_id')],
            'screenshot' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'utr.unique' => 'This transaction reference has already been submitted.',
            'utr.regex' => 'Enter a valid UTR / UPI transaction number using 6–64 letters or digits.',
        ];
    }
}
