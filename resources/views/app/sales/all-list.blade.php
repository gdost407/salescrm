@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2 py-3">
      <h5 class="mb-0">All Leads <span class="text-body-secondary fw-normal">({{ $leads->total() }})</span></h5>
      <form action="{{ route('sales-all-list') }}" method="GET" class="d-flex flex-wrap align-items-center gap-2" data-lead-search-form>
        <div class="input-group input-group-sm input-group-merge" style="max-width: 260px;">
          <span class="input-group-text"><i class="icon-base bx bx-search"></i></span>
          <input type="search" name="search" value="{{ $filters['search'] }}" class="form-control" placeholder="Search leads" aria-label="Search leads">
        </div>
        <button type="submit" class="btn btn-sm btn-primary" title="Search leads"><i class="icon-base bx bx-search"></i></button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEnd" aria-controls="offcanvasEnd" title="Filter leads"><i class="icon-base bx bx-filter-alt"></i></button>
        <a href="{{ route('sales-leads.export', request()->query()) }}" data-no-progress class="btn btn-sm btn-outline-secondary" title="Export filtered leads"><i class="icon-base bx bx-download"></i></a>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#importLeadModal" title="Import leads"><i class="icon-base bx bx-import"></i></button>
        <a href="{{ route('sales-create-lead') }}" class="btn btn-sm btn-primary" title="Create lead"><i class="icon-base bx bx-plus"></i></a>
      </form>
    </div>
    <div id="lead-results">
      @include('app.sales.partials.lead-results', compact('leads'))
    </div>
  </div>
</div>
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEnd" aria-labelledby="offcanvasEndLabel">
  <div class="offcanvas-header">
    <h5 id="offcanvasEndLabel" class="offcanvas-title">Filter</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close" ></button>
  </div>
  <div class="offcanvas-body mx-0 flex-grow-0">
    <form action="{{ route('sales-all-list') }}" method="GET" data-no-progress>
      <input type="hidden" name="search" value="{{ $filters['search'] }}">
      <div class="mb-6">
        <label class="form-label" for="basic-icon-default-fullname">Date Range</label>
        <div class="input-group input-group-merge">
          <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-user"></i></span>
          <select class="form-select" name="date_range" id="date_range" aria-label="Date range">
            <option value="">Any time</option>
            @foreach (['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', '3_month' => '3 Months', 'year' => 'This Year', 'custom' => 'Custom'] as $value => $label)
              <option value="{{ $value }}" @selected($filters['date_range'] === $value)>{{ $label }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="row g-2 mb-4" id="custom-date-range" @style(['display: none' => $filters['date_range'] !== 'custom'])>
        <div class="col-6">
          <label class="form-label" for="date_from">From</label>
          <input type="date" name="date_from" id="date_from" value="{{ $filters['date_from'] }}" class="form-control form-control-sm">
        </div>
        <div class="col-6">
          <label class="form-label" for="date_to">To</label>
          <input type="date" name="date_to" id="date_to" value="{{ $filters['date_to'] }}" class="form-control form-control-sm">
        </div>
      </div>
      <div class="mb-6">
        <label class="form-label" for="basic-icon-default-fullname">Lead Stage</label>
        <div class="input-group input-group-merge">
          <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-user"></i></span>
          <select class="form-select" name="stage" id="stage" aria-label="Lead stage">
            <option value="">All stages</option>
            @foreach ($filterOptions['stages'] as $option)
              <option value="{{ $option->name }}" @selected($filters['stage'] === $option->name)>{{ $option->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="mb-6">
        <label class="form-label" for="basic-icon-default-fullname">Lead Status</label>
        <div class="input-group input-group-merge">
          <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-user"></i></span>
          <select class="form-select" name="status" id="status" aria-label="Lead status">
            <option value="">All statuses</option>
            @foreach ($filterOptions['statuses'] as $option)
              <option value="{{ $option->name }}" @selected($filters['status'] === $option->name)>{{ $option->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="mb-6">
        <label class="form-label" for="basic-icon-default-fullname">Lead Source</label>
        <div class="input-group input-group-merge">
          <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-user"></i></span>
          <select class="form-select" name="source" id="source" aria-label="Lead source">
            <option value="">All sources</option>
            @foreach ($filterOptions['sources'] as $option)
              <option value="{{ $option->name }}" @selected($filters['source'] === $option->name)>{{ $option->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="mb-6">
        <label class="form-label" for="basic-icon-default-fullname">Contact Person</label>
        <div class="input-group input-group-merge">
          <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-user"></i></span>
          <select class="form-select" name="assigned_to" id="assigned_to" aria-label="Contact person">
            <option value="">All contact persons</option>
            @foreach ($filterOptions['users'] as $user)
              <option value="{{ $user->id }}" @selected((string) $filters['assigned_to'] === (string) $user->id)>{{ $user->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-sm btn-primary">Apply filters</button>
        <a href="{{ route('sales-all-list') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
      </div>
    </form>
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
          <p class="small text-body-secondary">Upload a CSV exported from Excel or Google Sheets. Required columns: name, email, mobile.</p>
          <a href="{{ route('sales-leads.import.sample') }}" data-no-progress class="btn btn-sm btn-outline-secondary mb-3"><i class="bx bx-download me-1"></i>Download sample sheet</a>
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

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const searchForm = $('[data-lead-search-form]');
    const results = $('#lead-results');
    const heading = $('.card-header h5');

    const loadLeads = (url, query = searchForm.serialize()) => {
      const button = searchForm.find('button[type="submit"]');
      button.prop('disabled', true);
      results.css('opacity', '0.55');
      const requestUrl = new URL(url, window.location.origin);
      requestUrl.search = query;

      $.ajax({
        url: requestUrl.toString(),
        method: 'GET',
        data: searchForm.serialize(),
        dataType: 'json',
        headers: { Accept: 'application/json' },
        success: (response) => {
          results.html(response.html);
          heading.find('span').text(`(${response.count})`);
          window.history.replaceState({}, '', `${requestUrl.pathname}${requestUrl.search}`);
        },
        error: () => window.alert('Unable to load leads.'),
        complete: () => {
          button.prop('disabled', false);
          results.css('opacity', '');
        },
      });
    };

    searchForm.on('submit', (event) => {
      event.preventDefault();
      loadLeads(searchForm.attr('action'));
    });

    results.on('click', '.pagination a', function (event) {
      event.preventDefault();
      const url = new URL(this.href);
      loadLeads(url.pathname, url.search);
    });

    const dateRange = document.getElementById('date_range');
    const customDateRange = document.getElementById('custom-date-range');

    const toggleCustomDates = () => {
      customDateRange.style.display = dateRange.value === 'custom' ? '' : 'none';
    };

    dateRange.addEventListener('change', toggleCustomDates);
    toggleCustomDates();
  });
</script>
@endpush

@endsection