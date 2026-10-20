@extends('layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Quotations</h4>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary" type="button" data-bs-toggle="offcanvas" data-bs-target="#quotation-filters" aria-controls="quotation-filters" title="Filter quotations"><i class="bx bx-filter-alt me-1"></i>Filter</button>
            <a class="btn btn-outline-success" href="{{ route('quotations.export', request()->query()) }}"><i class="bx bx-download me-1"></i>Export Excel</a>
            @if(auth()->user()->hasPermission('create_quotations'))
                <a class="btn btn-primary" href="{{ route('quotations.create') }}">Create Quotation</a>
            @endif
        </div>
    </div>
    @include('web.partials.alerts')

    <div class="offcanvas offcanvas-end" tabindex="-1" id="quotation-filters" aria-labelledby="quotation-filters-title">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="quotation-filters-title">Filter quotations</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form method="GET" action="{{ route('quotations.index') }}" class="d-flex flex-column gap-3">
                <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
                <div>
                    <label class="form-label" for="date_preset">Quotation date</label>
                    <select class="form-select" id="date_preset" name="date_preset">
                        <option value="">Any time</option>
                        <option value="today" @selected(($filters['date_preset'] ?? '') === 'today')>Today</option>
                        <option value="this_week" @selected(($filters['date_preset'] ?? '') === 'this_week')>This week</option>
                        <option value="last_7_days" @selected(($filters['date_preset'] ?? '') === 'last_7_days')>Last 7 days</option>
                        <option value="this_month" @selected(($filters['date_preset'] ?? '') === 'this_month')>This month</option>
                        <option value="this_year" @selected(($filters['date_preset'] ?? '') === 'this_year')>This year</option>
                        <option value="custom" @selected(($filters['date_preset'] ?? '') === 'custom')>Custom range</option>
                    </select>
                </div>
                <div class="custom-date-filter">
                    <label class="form-label" for="date_from">From</label>
                    <input class="form-control" type="date" id="date_from" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div class="custom-date-filter">
                    <label class="form-label" for="date_to">To</label>
                    <input class="form-control" type="date" id="date_to" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
                </div>
                <div>
                    <label class="form-label" for="status">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="d-flex gap-2 mt-2">
                    <button class="btn btn-primary flex-grow-1" type="submit">Apply filters</button>
                    <a class="btn btn-outline-secondary" href="{{ route('quotations.index') }}">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header py-2">
            <form method="GET" action="{{ route('quotations.index') }}" class="d-flex flex-wrap align-items-center gap-2">
                @foreach(['date_preset', 'date_from', 'date_to', 'status'] as $filter)
                    @if(!empty($filters[$filter]))
                        <input type="hidden" name="{{ $filter }}" value="{{ $filters[$filter] }}">
                    @endif
                @endforeach
                <div class="input-group input-group-sm" style="max-width: 360px">
                    <input class="form-control" type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search number, title, client..." aria-label="Search quotations">
                    <button class="btn btn-outline-primary" type="submit"><i class="bx bx-search"></i><span class="visually-hidden">Search</span></button>
                </div>
                @if(!empty($filters['search']))
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('quotations.index', request()->except(['search', 'page'])) }}">Clear search</a>
                @endif
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>SN</th><th>Number</th><th>Title</th><th>Client</th><th>Date</th><th>Status</th><th>Total (INR)</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($records as $record)
                        @php
                            $whatsAppNumber = preg_replace('/\D+/', '', (string) $record->client->mobile);
                            $shareMessage = 'Hello '.$record->client->name.', quotation '.$record->quotation_no.($record->title ? ' - '.$record->title : '').'. Total: ₹ '.number_format((float) $record->total_amount, 2).($record->valid_until ? ' Valid until: '.$record->valid_until->format('d M Y').'.' : '');
                        @endphp
                        <tr>
                            <td>{{ $records->firstItem() + $loop->index }}</td>
                            <td>{{ $record->quotation_no }}</td>
                            <td>{{ $record->title }}</td>
                            <td>{{ $record->client->name }}</td>
                            <td>{{ $record->quotation_date?->format('d M Y') }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $record->status)) }}</td>
                            <td>₹ {{ number_format((float) $record->total_amount, 2) }}</td>
                            <td>
                                <div>
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-quotation-popover aria-label="Quotation actions" aria-expanded="false">Actions <i class="bx bx-chevron-down ms-1"></i></button>
                                    <template data-quotation-actions>
                                        <ul class="list-unstyled mb-0">
                                        @if(auth()->user()->hasPermission('view_quotations'))
                                            <li><a class="d-block text-decoration-none px-2 py-1" href="{{ route('quotations.show', $record->id) }}"><i class="bx bx-show me-2"></i>View</a></li>
                                            <li><a class="d-block text-decoration-none px-2 py-1" href="{{ route('quotations.print', $record->id) }}" target="_blank" rel="noopener noreferrer"><i class="bx bx-printer me-2"></i>Print</a></li>
                                        @endif
                                        @if(auth()->user()->hasPermission('edit_quotations'))
                                            <li><a class="d-block text-decoration-none px-2 py-1" href="{{ route('quotations.edit', $record->id) }}"><i class="bx bx-edit-alt me-2"></i>Edit</a></li>
                                        @endif
                                        @if($whatsAppNumber !== '')
                                            <li><a class="d-block text-decoration-none px-2 py-1" href="https://wa.me/{{ $whatsAppNumber }}?text={{ rawurlencode($shareMessage) }}" target="_blank" rel="noopener noreferrer"><i class="bx bxl-whatsapp me-2"></i>WhatsApp</a></li>
                                        @endif
                                        @if($record->client->email)
                                            <li><a class="d-block text-decoration-none px-2 py-1" href="mailto:{{ $record->client->email }}?subject={{ rawurlencode('Quotation '.$record->quotation_no) }}&body={{ rawurlencode($shareMessage) }}"><i class="bx bx-envelope me-2"></i>Email</a></li>
                                        @endif
                                        @if(auth()->user()->hasPermission('delete_quotations'))
                                            <li><hr class="my-1"></li>
                                            <li><form method="POST" action="{{ route('quotations.destroy', $record->id) }}" class="quotation-delete-form px-2 py-1">@csrf @method('DELETE')<button class="btn btn-link text-danger p-0 text-decoration-none" type="submit"><i class="bx bx-trash me-2"></i>Delete</button></form></li>
                                        @endif
                                        </ul>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-4">No quotations found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body py-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <small class="text-body-secondary">Showing {{ $records->firstItem() ?? 0 }}–{{ $records->lastItem() ?? 0 }} of {{ $records->total() }} quotations</small>
            {{ $records->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const preset = document.getElementById('date_preset');
        const fields = document.querySelectorAll('.custom-date-filter');
        const toggleCustomDates = () => {
            fields.forEach((field) => field.classList.toggle('d-none', preset.value !== 'custom'));
        };
        const popovers = [];
        const popoverAllowList = {
            ...bootstrap.Popover.Default.allowList,
            a: [...bootstrap.Popover.Default.allowList.a, 'target', 'rel'],
            button: ['type', 'class'],
            form: ['action', 'method', 'class'],
            input: ['type', 'name', 'value'],
        };

        preset.addEventListener('change', toggleCustomDates);
        toggleCustomDates();

        document.querySelectorAll('[data-quotation-popover]').forEach((button) => {
            const template = button.parentElement.querySelector('template[data-quotation-actions]');
            if (!template) return;

            const popover = new bootstrap.Popover(button, {
                allowList: popoverAllowList,
                boundary: 'viewport',
                container: 'body',
                content: () => template.innerHTML,
                html: true,
                placement: 'auto',
                trigger: 'manual',
            });
            popovers.push({ button, popover });
            button.addEventListener('shown.bs.popover', () => button.setAttribute('aria-expanded', 'true'));
            button.addEventListener('hidden.bs.popover', () => button.setAttribute('aria-expanded', 'false'));
            button.addEventListener('click', (event) => {
                event.stopPropagation();
                popovers.forEach((entry) => {
                    if (entry.button !== button) entry.popover.hide();
                });
                popover.toggle();
            });
        });
        document.addEventListener('click', (event) => {
            if (!event.target.closest('[data-quotation-popover], .popover')) {
                popovers.forEach((entry) => entry.popover.hide());
            }
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') popovers.forEach((entry) => entry.popover.hide());
        });
        document.addEventListener('submit', (event) => {
            if (event.target.matches('.quotation-delete-form') && !window.confirm('Delete this quotation?')) {
                event.preventDefault();
            }
        });
    });
</script>
@endsection
