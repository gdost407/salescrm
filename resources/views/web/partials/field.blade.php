@php
    $inputType = $inputType ?? 'text';
    $required = $required ?? false;
    $icon = $icon ?? match ($field) {
        'name', 'client_id' => 'bx-user',
        'company_name' => 'bx-buildings',
        'email' => 'bx-envelope',
        'mobile' => 'bx-phone',
        'country' => 'bx-globe',
        'billing_address', 'shipping_address', 'state', 'city' => 'bx-map',
        'zip_code' => 'bx-pin',
        'gst_no', 'pan_no' => 'bx-id-card',
        'notes', 'description', 'terms' => 'bx-note',
        'status', 'is_active' => 'bx-check-circle',
        'amount', 'rate' => 'bx-money',
        'gst_rate', 'tax_id' => 'bx-percentage',
        'type', 'ledger_type', 'entry_type', 'transaction_type' => 'bx-category',
        'payment_mode' => 'bx-credit-card',
        'document', 'quotation_id', 'job_id', 'invoice_id' => 'bx-file',
        default => $inputType === 'date' ? 'bx-calendar' : 'bx-hash',
    };
    $value = old($field, $value ?? data_get($record, $field));
    if ($value instanceof \DateTimeInterface) {
        $value = $value->format('Y-m-d');
    }
@endphp
<div class="mb-6">
    <label class="form-label" for="{{ $field }}">{{ $label }}</label>
    <div class="input-group input-group-merge">
        <span class="input-group-text {{ $required ? 'text-danger' : '' }}" aria-hidden="true"><i class="icon-base bx {{ $icon }}"></i></span>
    @if ($inputType === 'select')
        <select class="form-select @error($field) is-invalid @enderror" id="{{ $field }}" name="{{ $field }}" @required($required) @error($field) aria-invalid="true" aria-describedby="{{ $field }}-error" @enderror>
            @foreach ($options as $key => $text)
            <option value="{{ $key }}" @selected((string) $value === (string) $key)>{{ $text }}</option>
            @endforeach
        </select>
    @elseif ($inputType === 'textarea')
        <textarea class="form-control @error($field) is-invalid @enderror" id="{{ $field }}" name="{{ $field }}" rows="3" @required($required) @error($field) aria-invalid="true" aria-describedby="{{ $field }}-error" @enderror>{{ $value }}</textarea>
    @else
        <input class="form-control @error($field) is-invalid @enderror" type="{{ $inputType }}" id="{{ $field }}" name="{{ $field }}" value="{{ $value }}" @required($required) @error($field) aria-invalid="true" aria-describedby="{{ $field }}-error" @enderror @if($inputType === 'number') step="{{ $step ?? '0.01' }}" min="0" @endif>
    @endif
    </div>
    @error($field)<div class="invalid-feedback d-block" id="{{ $field }}-error">{{ $message }}</div>@enderror
</div>
