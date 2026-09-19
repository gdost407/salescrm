@php
    $inputType = $inputType ?? 'text';
    $value = old($field, $value ?? data_get($record, $field));
    if ($value instanceof \DateTimeInterface) {
        $value = $value->format('Y-m-d');
    }
@endphp
<div class="mb-3">
    <label class="form-label" for="{{ $field }}">{{ $label }}</label>
    @if ($inputType === 'select')
        <select class="form-select @error($field) is-invalid @enderror" id="{{ $field }}" name="{{ $field }}">
            @foreach ($options as $key => $text)
            <option value="{{ $key }}" @selected((string) $value === (string) $key)>{{ $text }}</option>
            @endforeach
        </select>
    @elseif ($inputType === 'textarea')
        <textarea class="form-control @error($field) is-invalid @enderror" id="{{ $field }}" name="{{ $field }}" rows="3">{{ $value }}</textarea>
    @else
        <input class="form-control @error($field) is-invalid @enderror" type="{{ $inputType }}" id="{{ $field }}" name="{{ $field }}" value="{{ $value }}" @if($inputType === 'number') step="{{ $step ?? '0.01' }}" min="0" @endif>
    @endif
    @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
