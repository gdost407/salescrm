<form method="POST" action="{{ $record->exists ? route($resource.'.update', $record->id) : route($resource.'.store') }}">
@csrf
@if($record->exists) @method('PUT') @endif
<div class="row">
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['required' => true, 'field' => 'name', 'label' => 'Name', 'inputType' => 'text'])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['required' => true, 'field' => 'type', 'label' => 'Type', 'inputType' => 'select', 'options' => ['business' => 'Business', 'individual' => 'Individual']])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['field' => 'company_name', 'label' => 'Company name', 'inputType' => 'text'])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['field' => 'email', 'label' => 'Email', 'inputType' => 'email'])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['field' => 'mobile', 'label' => 'Mobile', 'inputType' => 'tel'])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['field' => 'client_code', 'label' => 'Client code', 'inputType' => 'text'])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['field' => 'gst_no', 'label' => 'GST number', 'inputType' => 'text'])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['field' => 'pan_no', 'label' => 'PAN number', 'inputType' => 'text'])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['required' => true, 'field' => 'is_active', 'label' => 'Status', 'inputType' => 'select', 'options' => ['0' => 'Inactive', '1' => 'Active'], 'value' => $record->exists ? $record->is_active : 1])</div>
<div class="col-12 col-md-6">@include('web.partials.field', ['field' => 'billing_address', 'label' => 'Billing address', 'inputType' => 'textarea'])</div>
<div class="col-12 col-md-6">@include('web.partials.field', ['field' => 'shipping_address', 'label' => 'Shipping address', 'inputType' => 'textarea'])</div>
<div class="col-12 col-md-6 col-lg-3">
<div class="mb-6"><label class="form-label" for="clientCountry">Country</label><div class="input-group input-group-merge"><span class="input-group-text" aria-hidden="true"><i class="icon-base bx bx-globe"></i></span><select class="form-select location-select @error('country') is-invalid @enderror" id="clientCountry" name="country" data-placeholder="Select country" aria-label="Country" @error('country') aria-invalid="true" aria-describedby="country-error" @enderror><option value="">Select country</option></select></div>@error('country')<div class="invalid-feedback d-block" id="country-error">{{ $message }}</div>@enderror</div>
</div>
<div class="col-12 col-md-6 col-lg-3">
<div class="mb-6"><label class="form-label" for="clientState">State</label><div class="input-group input-group-merge"><span class="input-group-text" aria-hidden="true"><i class="icon-base bx bx-map"></i></span><select class="form-select location-select @error('state') is-invalid @enderror" id="clientState" name="state" data-placeholder="Select state" data-selected="{{ old('state', $record->state ?? '') }}" aria-label="State" @error('state') aria-invalid="true" aria-describedby="state-error" @enderror><option value="">Select state</option></select></div>@error('state')<div class="invalid-feedback d-block" id="state-error">{{ $message }}</div>@enderror</div>
</div>
<div class="col-12 col-md-6 col-lg-3">
<div class="mb-6"><label class="form-label" for="clientCity">City</label><div class="input-group input-group-merge"><span class="input-group-text" aria-hidden="true"><i class="icon-base bx bx-map"></i></span><select class="form-select location-select @error('city') is-invalid @enderror" id="clientCity" name="city" data-placeholder="Select city" data-selected="{{ old('city', $record->city ?? '') }}" aria-label="City" @error('city') aria-invalid="true" aria-describedby="city-error" @enderror><option value="">Select city</option></select></div>@error('city')<div class="invalid-feedback d-block" id="city-error">{{ $message }}</div>@enderror</div>
</div>
<div class="col-12 col-md-6 col-lg-3">@include('web.partials.field', ['field' => 'zip_code', 'label' => 'Postal code', 'inputType' => 'text'])</div>
<div class="col-12">@include('web.partials.field', ['field' => 'notes', 'label' => 'Notes', 'inputType' => 'textarea'])</div>
</div>
<div class="d-flex flex-wrap gap-2"><button type="submit" class="btn btn-primary"><i class="bx bx-save me-1" aria-hidden="true"></i>Save Client</button>
@if(auth()->user()->hasPermission('view_'.$permission))<a href="{{ route($resource.'.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1" aria-hidden="true"></i>Back to list</a>@endif
</div></form>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.bootstrap5.min.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    const countrySelect = document.getElementById('clientCountry');
    const stateSelect = document.getElementById('clientState');
    const citySelect = document.getElementById('clientCity');
    const selectedCountry = @json(old('country', $record->country ?? 'India'));
    const selectedState = stateSelect?.dataset.selected || '';
    const selectedCity = citySelect?.dataset.selected || '';

    if (!countrySelect || !stateSelect || !citySelect) {
        return;
    }

    const searchableSelects = new Map([countrySelect, stateSelect, citySelect].map((select) => [
        select,
        typeof window.TomSelect === 'function' ? new window.TomSelect(select, {
            create: false,
            maxOptions: null,
            searchField: ['text'],
            placeholder: select.dataset.placeholder,
        }) : null,
    ]));

    const setOptions = (select, values, placeholder, selectedValue = '') => {
        const searchableSelect = searchableSelects.get(select);
        const options = values.map((item) => {
            const name = typeof item === 'string' ? item : item.name;
            return { value: name, text: name };
        });
        const normalize = (value) => String(value ?? '').trim().toLocaleLowerCase();
        const match = selectedValue && options.find((option) => normalize(option.value) === normalize(selectedValue));
        if (match) {
            selectedValue = match.value;
        } else if (selectedValue) {
            options.push({ value: selectedValue, text: `${selectedValue} (saved value)` });
        }

        if (searchableSelect) {
            searchableSelect.clear(true);
            searchableSelect.clearOptions();
            searchableSelect.addOptions(options);
            searchableSelect.settings.placeholder = placeholder;
            searchableSelect.setValue(selectedValue || '', true);
            searchableSelect.refreshOptions(false);
            searchableSelect.enable();
        } else {
            select.replaceChildren(new Option(placeholder, ''), ...options.map((option) => new Option(option.text, option.value)));
            select.value = selectedValue || '';
            select.disabled = false;
        }
    };

    const getLocations = async (url, params = {}) => {
        const response = await fetch(`${url}?${new URLSearchParams(params)}`, {
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const payload = await response.json();
        if (!response.ok || !Array.isArray(payload.data)) {
            throw new Error(payload.message || 'Unable to load locations');
        }
        return payload.data;
    };

    let statesRequest = 0;
    let citiesRequest = 0;
    let countries = [];
    let states = [];

    const loadCities = async (selectedValue = '') => {
        const request = ++citiesRequest;
        const country = countrySelect.value;
        const state = stateSelect.value;
        setOptions(citySelect, [], 'Select city', selectedValue);
        if (!states.some((item) => item.name === state)) return;
        try {
            const cities = await getLocations('{{ route('locations.cities') }}', { country, state });
            if (request === citiesRequest) setOptions(citySelect, cities, 'Select city', citySelect.value);
        } catch (error) {
            if (request === citiesRequest) setOptions(citySelect, [], 'Unable to load cities — select state again to retry', citySelect.value);
            console.error(error);
        }
    };

    const loadStates = async (selectedValue = '', cityValue = '') => {
        const request = ++statesRequest;
        ++citiesRequest;
        const country = countrySelect.value;
        states = [];
        setOptions(stateSelect, [], 'Select state', selectedValue);
        setOptions(citySelect, [], 'Select city', cityValue);
        if (!countries.some((item) => item.name === country)) return;
        try {
            const result = await getLocations('{{ route('locations.states') }}', { country });
            if (request !== statesRequest) return;
            states = result;
            setOptions(stateSelect, states, 'Select state', stateSelect.value);
            await loadCities(citySelect.value);
        } catch (error) {
            if (request === statesRequest) setOptions(stateSelect, [], 'Unable to load states — select country again to retry', stateSelect.value);
            console.error(error);
        }
    };

    countrySelect.addEventListener('change', () => loadStates());
    stateSelect.addEventListener('change', () => loadCities());
    setOptions(countrySelect, [], 'Select country', selectedCountry);
    setOptions(stateSelect, [], 'Select state', selectedState);
    setOptions(citySelect, [], 'Select city', selectedCity);

    try {
        countries = await getLocations('{{ route('locations.countries') }}');
        setOptions(countrySelect, countries, 'Select country', countrySelect.value);
        await loadStates(stateSelect.value, citySelect.value);
    } catch (error) {
        setOptions(countrySelect, [], 'Unable to load countries — reload to retry', countrySelect.value);
        console.error(error);
    }
});
</script>
@endpush
