@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h5 class="mb-0">{{ $lead ? 'Edit Lead' : 'Lead Creation' }}</h5>
      <small class="text-body-secondary float-end">* Make sure to fill all required fields</small>
    </div>
    <div class="card-body">
      <form action="{{ $lead ? route('sales-leads.update', $lead) : route('sales-leads.store') }}" method="POST">
        @csrf
        @if ($lead)
          @method('PUT')
        @endif
        <div class="row">
          <div class="col-sm-4">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Full Name</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-user"></i></span>
                <input type="text" name="name" class="form-control" id="basic-icon-default-fullname" placeholder="John Doe" value="{{ old('name', $lead?->name) }}" aria-label="John Doe" aria-describedby="basic-icon-default-fullname2" required>
              </div>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Email</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-envelope"></i></span>
                <input type="email" name="email" class="form-control" id="basic-icon-default-fullname" placeholder="john.doe@example.com" value="{{ old('email', $lead?->email) }}" aria-label="john.doe@example.com" aria-describedby="basic-icon-default-fullname2">
              </div>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Mobile Number</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-phone"></i></span>
                <input type="text" name="mobile" class="form-control" id="basic-icon-default-fullname" placeholder="123-456-7890" value="{{ old('mobile', $lead?->mobile) }}" aria-label="123-456-7890" aria-describedby="basic-icon-default-fullname2">
              </div>
            </div>
          </div>
          <div class="col-sm-8">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Job Title</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-briefcase"></i></span>
                <input type="text" name="job_title" class="form-control" id="basic-icon-default-fullname" placeholder="Job title" value="{{ old('job_title', $lead?->job_title) }}" aria-label="Job title" aria-describedby="basic-icon-default-fullname2">
              </div>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Order Value</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-dollar"></i></span>
                <input type="number" name="deal_amount" class="form-control" id="basic-icon-default-fullname" placeholder="Order value" value="{{ old('deal_amount', $lead?->deal_amount) }}" min="0" step="0.01" aria-label="Order value" aria-describedby="basic-icon-default-fullname2">
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Lead Stage</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-star"></i></span>
                <select name="stage" class="form-select" id="exampleFormControlSelect1" aria-label="Lead stage" required>
                  @foreach ($settings->get('stage', collect()) as $setting)
                    <option value="{{ $setting->name }}" @selected(old('stage', $lead?->stage ?? 'New') === $setting->name)>{{ $setting->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Lead Status</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-check"></i></span>
                <select name="status" class="form-select" id="exampleFormControlSelect1" aria-label="Lead status" required>
                  @foreach ($settings->get('status', collect()) as $setting)
                    <option value="{{ $setting->name }}" @selected(old('status', $lead?->status ?? 'New') === $setting->name)>{{ $setting->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Lead Source</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-globe"></i></span>
                <select name="source" class="form-select" id="exampleFormControlSelect1" aria-label="Lead source" required>
                  @foreach ($settings->get('source', collect()) as $setting)
                    <option value="{{ $setting->name }}" @selected(old('source', $lead?->source ?? 'Self') === $setting->name)>{{ $setting->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Contact Person</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-user"></i></span>
                <select name="assigned_to" class="form-select" id="exampleFormControlSelect1" aria-label="Contact person">
                  <option value="">Unassigned</option>
                  @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected((string) old('assigned_to', $lead?->assigned_to) === (string) $user->id)>{{ $user->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Address</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-map"></i></span>
                <input type="text" name="address" class="form-control" id="basic-icon-default-fullname" placeholder="Address" value="{{ old('address', $lead?->address) }}" aria-label="Address" aria-describedby="basic-icon-default-fullname2">
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="leadCountry">Country</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-globe"></i></span>
                <select name="country" class="form-select location-select" id="leadCountry" data-placeholder="Select country" aria-label="Country">
                  <option value="">Loading countries...</option>
                </select>
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="leadState">State</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-map"></i></span>
                <select name="state" class="form-select location-select" id="leadState" data-placeholder="Select state" aria-label="State" data-selected="{{ old('state', $lead?->state ?? '') }}">
                  <option value="">Select state</option>
                </select>
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="leadCity">City</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-map"></i></span>
                <select name="city" class="form-select location-select" id="leadCity" data-placeholder="Select city" aria-label="City" data-selected="{{ old('city', $lead?->city ?? '') }}">
                  <option value="">Select city</option>
                </select>
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Zipcode</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-pin"></i></span>
                <input type="text" name="pincode" class="form-control" id="basic-icon-default-fullname" placeholder="123456" value="{{ old('pincode', $lead?->pincode) }}" aria-label="123456" aria-describedby="basic-icon-default-fullname2">
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Extra Description</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-note"></i></span>
                <input type="text" name="description" class="form-control" id="basic-icon-default-fullname" placeholder="Extra details about the lead" value="{{ old('description', $lead?->description) }}" aria-label="Extra details about the lead" aria-describedby="basic-icon-default-fullname2">
              </div>
            </div>
          </div>
          <div class="col-sm-12">
            <div class="mb-6">
              <button type="submit" class="btn btn-primary">
                <i class="bx bx-save"></i> Save Lead
              </button>
              <a href="{{ route('sales-all-list') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back"></i> Cancel
              </a>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

@if (! $lead)
<div class="container-xxl pb-4">
  <div class="card border">
    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3 py-3">
      <div>
        <h6 class="mb-1">Import leads from sheet</h6>
        <small class="text-body-secondary">Upload a CSV file from Excel or Google Sheets.</small>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('sales-leads.import.sample') }}" data-no-progress class="btn btn-sm btn-outline-secondary">
          <i class="bx bx-download me-1"></i> Download sample sheet
        </a>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#importLeadModal">
          <i class="bx bx-upload me-1"></i> Import leads
        </button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="importLeadModal" tabindex="-1" aria-labelledby="importLeadModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="importLeadModalLabel">Import Leads</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('sales-leads.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <p class="small text-body-secondary">Required columns: name, email, mobile.</p>
          <input type="file" name="file" class="form-control" accept=".csv,.txt,text/csv" required>
          @if ($errors->has('file'))
            <div class="alert alert-danger mt-3 mb-0">
              @foreach ($errors->get('file') as $error)
                <div>{{ $error }}</div>
              @endforeach
            </div>
          @endif
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-primary"><i class="bx bx-upload me-1"></i>Import</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.bootstrap5.min.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', async () => {
    const countrySelect = document.getElementById('leadCountry');
    const stateSelect = document.getElementById('leadState');
    const citySelect = document.getElementById('leadCity');
    const selectedCountry = @json(old('country', $lead?->country ?? 'India'));
    const selectedState = stateSelect?.dataset.selected || '';
    const selectedCity = citySelect?.dataset.selected || '';

    if (!countrySelect || !stateSelect || !citySelect || !window.appAjax) {
      return;
    }

    const searchableSelects = new Map(
      [countrySelect, stateSelect, citySelect].map((select) => [
        select,
        new TomSelect(select, {
          create: false,
          maxOptions: null,
          searchField: ['text'],
          placeholder: select.dataset.placeholder,
        }),
      ]),
    );

    const setOptions = (select, values, placeholder, selectedValue = '') => {
      const searchableSelect = searchableSelects.get(select);
      const options = values.map((item) => {
        const name = typeof item === 'string' ? item : item.name;

        return { value: name, text: name };
      });

      searchableSelect.clearOptions();
      searchableSelect.addOptions(options);
      searchableSelect.setValue(selectedValue, true);
      searchableSelect.settings.placeholder = placeholder;
      searchableSelect.refreshOptions(false);

      if (values.length === 0) {
        searchableSelect.disable();
      } else {
        searchableSelect.enable();
      }
    };

    const loadStates = async (country, selectedValue = '') => {
      stateSelect.disabled = true;
      citySelect.disabled = true;
      setOptions(stateSelect, [], 'Loading states...');
      setOptions(citySelect, [], 'Select city');

      const states = await window.appAjax.get('{{ route('locations.states') }}', { country });
      setOptions(stateSelect, states, 'Select state', selectedValue);
    };

    const loadCities = async (country, state, selectedValue = '') => {
      citySelect.disabled = true;
      setOptions(citySelect, [], 'Loading cities...');

      const cities = await window.appAjax.get('{{ route('locations.cities') }}', { country, state });
      setOptions(citySelect, cities, 'Select city', selectedValue);
    };

    countrySelect.addEventListener('change', async () => {
      if (!countrySelect.value) {
        setOptions(stateSelect, [], 'Select state');
        setOptions(citySelect, [], 'Select city');
        return;
      }

      try {
        await loadStates(countrySelect.value);
      } catch (error) {
        setOptions(stateSelect, [], 'Unable to load states');
        console.error(error);
      }
    });

    stateSelect.addEventListener('change', async () => {
      if (!stateSelect.value) {
        setOptions(citySelect, [], 'Select city');
        return;
      }

      try {
        await loadCities(countrySelect.value, stateSelect.value);
      } catch (error) {
        setOptions(citySelect, [], 'Unable to load cities');
        console.error(error);
      }
    });

    try {
      const countries = await window.appAjax.get('{{ route('locations.countries') }}');
      setOptions(countrySelect, countries, 'Select country', selectedCountry);

      if (countrySelect.value) {
        await loadStates(countrySelect.value, selectedState);

        if (stateSelect.value) {
          await loadCities(countrySelect.value, stateSelect.value, selectedCity);
        }
      }
    } catch (error) {
      setOptions(countrySelect, [], 'Unable to load countries');
      console.error(error);
    }
  });
</script>
@endpush