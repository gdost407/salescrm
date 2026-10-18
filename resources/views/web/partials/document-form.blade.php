<form method="POST" enctype="multipart/form-data" action="{{ $record->exists ? route($resource.'.update', $record->id) : route($resource.'.store') }}">
@csrf @if($record->exists) @method('PUT') @endif
<div class="row">
<div class="col-md-6">
@if($type === 'quotation' && ! $record->exists)
    <div class="mb-6">
        <label class="form-label" for="client_id">Client</label>
        <div class="input-group input-group-merge">
            <span class="input-group-text text-danger" aria-hidden="true"><i class="icon-base bx bx-user"></i></span>
            <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id" data-search-url="{{ route('quotations.clients.search') }}" required @error('client_id') aria-invalid="true" aria-describedby="client_id-error" @enderror>
                <option value="">Select client</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}" @selected((string) old('client_id') === (string) $client->id)>{{ $client->name }}</option>
                @endforeach
            </select>
        </div>
        @error('client_id')<div class="invalid-feedback d-block" id="client_id-error">{{ $message }}</div>@enderror
    </div>
    <div class="card bg-lighter mb-6" id="quotationClientDetails" aria-live="polite">
        <div class="card-body py-3">
            <h6 class="mb-3">Client contact and address</h6>
            <div class="row g-2 small">
                <div class="col-md-6"><strong>Name:</strong> <span data-client-detail="name">—</span></div>
                <div class="col-md-6"><strong>Company:</strong> <span data-client-detail="company_name">—</span></div>
                <div class="col-md-6"><strong>Mobile:</strong> <span data-client-detail="mobile">—</span></div>
                <div class="col-md-6"><strong>Email:</strong> <span data-client-detail="email">—</span></div>
                <div class="col-md-6"><strong>Billing address:</strong> <span data-client-detail="billing_address">—</span></div>
                <div class="col-md-6"><strong>Shipping address:</strong> <span data-client-detail="shipping_address">—</span></div>
                <div class="col-md-6"><strong>Location:</strong> <span data-client-detail="location">—</span></div>
                <div class="col-md-6"><strong>Postal code:</strong> <span data-client-detail="zip_code">—</span></div>
            </div>
        </div>
    </div>
@else
    @include('web.partials.field', ['required' => true, 'field' => 'client_id', 'label' => 'Client', 'inputType' => 'select', 'options' => ['' => 'Select client'] + $clients->pluck('name', 'id')->all()])
@endif
</div>
<div class="col-md-3">@include('web.partials.field', ['required' => true, 'field' => $type.'_date', 'label' => ucfirst($type).' date', 'inputType' => 'date', 'value' => $record->{$type.'_date'} ?? now()->format('Y-m-d')])</div>
<div class="col-md-3">@include('web.partials.field', ['field' => 'status', 'label' => 'Status', 'inputType' => 'select', 'options' => array_combine($statuses, array_map(fn ($status) => ucfirst(str_replace('_', ' ', $status)), $statuses))])</div>
@foreach(\App\Actions\SalesDocumentChoices::FIELDS[$type] as $field)
    @if($field === 'terms') @continue @endif
    <div class="col-md-6">
    @if(isset($linkedDocuments[$field]))
    @include('web.partials.field', ['field' => $field, 'label' => ucfirst(str_replace('_id', '', $field)), 'inputType' => 'select', 'options' => ['' => 'None'] + $linkedDocuments[$field]->pluck(str_replace('_id', '_no', $field), 'id')->all()])
    @else
    @include('web.partials.field', ['field' => $field, 'label' => $field === 'title' ? 'Quotation title' : ucfirst(str_replace('_', ' ', $field)), 'inputType' => $field === 'valid_until' ? 'date' : 'text', 'value' => $field === 'valid_until' ? ($record->valid_until ?? ($record->exists ? $record->quotation_date?->copy()->addDays(2) : now()->addDays(2)->format('Y-m-d'))) : $record->{$field}])
    @endif
    </div>
@endforeach
</div>
@include('web.partials.item-rows')
@include('web.partials.totals', ['editing' => true])
<div class="row">
<div class="{{ $type === 'job' ? 'col-12' : 'col-md-6' }}">@include('web.partials.field', ['field' => 'notes', 'label' => 'Notes', 'inputType' => 'textarea'])</div>
@if($type !== 'job') <div class="col-md-6">@include('web.partials.field', ['field' => 'terms', 'label' => 'Terms', 'inputType' => 'textarea'])</div> @endif
</div>
@include('web.partials.attachments', ['editing' => true])
<div class="d-flex flex-wrap gap-2">
<button type="submit" class="btn btn-primary"><i class="bx bx-save me-1" aria-hidden="true"></i>Save {{ ucfirst($type) }}</button>
@if(auth()->user()->hasPermission('view_'.$resource))<a class="btn btn-outline-secondary" href="{{ route($resource.'.index') }}"><i class="bx bx-arrow-back me-1" aria-hidden="true"></i>Back to list</a>@endif
</div>
</form>
@include('web.partials.calculations')

@if($type === 'quotation' && ! $record->exists)
    @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.bootstrap5.min.css">
    @endpush
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const select = document.getElementById('client_id');
        const details = document.getElementById('quotationClientDetails');
        if (!select || !details || typeof window.TomSelect !== 'function') return;

        const clientDetails = @json($clients->keyBy('id'));
        const setClientDetails = (client) => {
            const display = (value) => value || '—';
            details.querySelector('[data-client-detail="name"]').textContent = display(client?.name);
            details.querySelector('[data-client-detail="company_name"]').textContent = display(client?.company_name);
            details.querySelector('[data-client-detail="mobile"]').textContent = display(client?.mobile);
            details.querySelector('[data-client-detail="email"]').textContent = display(client?.email);
            details.querySelector('[data-client-detail="billing_address"]').textContent = display(client?.billing_address);
            details.querySelector('[data-client-detail="shipping_address"]').textContent = display(client?.shipping_address);
            details.querySelector('[data-client-detail="location"]').textContent = [client?.city, client?.state, client?.country].filter(Boolean).join(', ') || '—';
            details.querySelector('[data-client-detail="zip_code"]').textContent = display(client?.zip_code);
        };

        const searchableSelect = new window.TomSelect(select, {
            create: false,
            maxOptions: 20,
            valueField: 'value',
            labelField: 'text',
            searchField: ['text', 'mobile', 'email'],
            placeholder: 'Search clients by name, mobile, or email',
            load: async (query, callback) => {
                try {
                    const response = await fetch(`${select.dataset.searchUrl}?${new URLSearchParams({ q: query })}`, {
                        credentials: 'same-origin',
                        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    const payload = await response.json();
                    if (!response.ok || !Array.isArray(payload.data)) throw new Error(payload.message || 'Unable to search clients');
                    callback(payload.data.map((client) => {
                        clientDetails[client.id] = client;
                        return { value: String(client.id), text: client.name, mobile: client.mobile || '', email: client.email || '' };
                    }));
                } catch (error) {
                    console.error(error);
                    callback();
                }
            },
            onChange(clientId) {
                setClientDetails(clientDetails[clientId] || this.options[clientId]);
            },
        });

        setClientDetails(clientDetails[select.value]);
    });
    </script>
    @endpush
@endif
