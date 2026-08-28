<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'activity_type' => ['required', Rule::in(['notes', 'call', 'followup', 'visit', 'gmeet', 'email'])],
            'subject' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:65535'],
            'followup_date' => ['nullable', 'date', Rule::requiredIf(fn () => $this->activity_type === 'followup')],
            'followup_time' => ['nullable', 'date_format:H:i', Rule::requiredIf(fn () => $this->activity_type === 'followup')],
            'visit_address' => ['nullable', 'string', 'max:65535', Rule::requiredIf(fn () => $this->activity_type === 'visit')],
            'visit_country' => ['nullable', 'string', 'max:255', Rule::requiredIf(fn () => $this->activity_type === 'visit')],
            'visit_state' => ['nullable', 'string', 'max:255', Rule::requiredIf(fn () => $this->activity_type === 'visit')],
            'visit_city' => ['nullable', 'string', 'max:255', Rule::requiredIf(fn () => $this->activity_type === 'visit')],
            'visit_zip' => ['nullable', 'string', 'max:20', Rule::requiredIf(fn () => $this->activity_type === 'visit')],
            'visit_motive' => ['nullable', 'string', 'max:65535', Rule::requiredIf(fn () => $this->activity_type === 'visit')],
            'visit_scheduled_at' => ['nullable', 'date', Rule::requiredIf(fn () => $this->activity_type === 'visit')],
            'mark_as_lead_address' => ['nullable', 'boolean'],
            'meeting_link' => ['nullable', 'url', 'max:2048', Rule::requiredIf(fn () => $this->activity_type === 'gmeet')],
            'meeting_scheduled_at' => ['nullable', 'date', Rule::requiredIf(fn () => $this->activity_type === 'gmeet')],
            'meeting_motive' => ['nullable', 'string', 'max:65535', Rule::requiredIf(fn () => $this->activity_type === 'gmeet')],
            'email_subject' => ['nullable', 'string', 'max:255', Rule::requiredIf(fn () => $this->activity_type === 'email')],
            'email_body' => ['nullable', 'string', 'max:65535', Rule::requiredIf(fn () => $this->activity_type === 'email')],
            'attachment' => ['nullable', 'file', 'max:10240'],
        ];
    }
}
