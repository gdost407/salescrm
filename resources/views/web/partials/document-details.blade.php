@php($documentTypeLabel = ucfirst($type))
@php($documentNumber = $record->{$type.'_no'})

<style>
    .sales-document {
        --document-accent: #6554c0;
        --document-accent-soft: #f3f0ff;
        --document-accent-border: #ded8ff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        color: #253047;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 8px 24px rgb(31 41 55 / 7%);
    }
    .sales-document--job {
        --document-accent: #1677c8;
        --document-accent-soft: #edf6fd;
        --document-accent-border: #cfe7f8;
    }
    .sales-document--invoice {
        --document-accent: #16835d;
        --document-accent-soft: #edf8f2;
        --document-accent-border: #cdebdc;
    }
    .sales-document__header {
        position: relative;
        overflow: hidden;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e8ebf0;
    }
    .sales-document__diagonal {
        position: absolute;
        top: 5px;
        right: -18px;
        width: 80px;
        height: 7px;
        transform: rotate(38deg);
        background: var(--document-accent);
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .sales-document__company-name {
        color: #1e293b;
        font-size: 1.4rem;
        font-weight: 700;
        letter-spacing: -.02em;
    }
    .sales-document__company-meta,
    .sales-document__client-meta {
        color: #64748b;
        line-height: 1.6;
        overflow-wrap: anywhere;
    }
    .sales-document__type {
        color: var(--document-accent);
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .16em;
        text-transform: uppercase;
    }
    .sales-document__title {
        color: #1e293b;
        font-size: 1.8rem;
        font-weight: 700;
        line-height: 1.2;
        margin: .35rem 0;
        overflow-wrap: anywhere;
    }
    .sales-document__header-right,
    .sales-document__header > .row > .col-md-7 {
        padding-right: 82px;
    }
    .sales-document__number {
        color: #64748b;
        font-size: .9rem;
        font-weight: 600;
    }
    .sales-document__status {
        display: inline-block;
        border: 1px solid var(--document-accent-border);
        border-radius: 99px;
        color: var(--document-accent);
        background: var(--document-accent-soft);
        padding: .25rem .7rem;
        font-size: .75rem;
        font-weight: 700;
        text-transform: capitalize;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .sales-document__meta-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .5rem 1rem;
        margin-top: 1.15rem;
        text-align: left;
    }
    .sales-document__meta-label,
    .sales-document__section-label {
        color: #64748b;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
    }
    .sales-document__meta-value {
        color: #253047;
        font-size: .86rem;
        font-weight: 600;
    }
    .sales-document__party {
        height: 100%;
        border: 1px solid #e8ebf0;
        border-radius: 8px;
        padding: 1rem 1.1rem;
    }
    .sales-document__party-name {
        color: #253047;
        font-size: 1rem;
        font-weight: 700;
        margin: .35rem 0 .25rem;
    }
    .sales-document__items {
        border-color: #e5e7eb;
        font-size: .82rem;
        margin-bottom: 0;
    }
    .sales-document__items thead th {
        background: var(--document-accent-soft);
        border-bottom: 2px solid var(--document-accent);
        color: #344054;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        vertical-align: middle;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .sales-document__items td,
    .sales-document__items th {
        padding: .7rem .55rem;
    }
    .sales-document__items tbody tr {
        break-inside: avoid;
    }
    .sales-document__totals .table {
        margin-bottom: 0;
    }
    .sales-document__totals > .row > div {
        width: 100%;
    }
    .sales-document__totals .table > :not(caption) > * > * {
        padding: .55rem .7rem;
    }
    .sales-document__grand-total th {
        background: var(--document-accent-soft);
        color: #1e293b;
        border-top: 2px solid var(--document-accent);
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .sales-document__notes {
        white-space: pre-line;
        color: #475569;
        overflow-wrap: anywhere;
    }
    .sales-document__signature {
        min-width: 220px;
        margin-top: 3.5rem;
        padding-top: .65rem;
        border-top: 1px solid #94a3b8;
        display: inline-block;
        text-align: center;
    }
    @media (max-width: 575.98px) {
        .sales-document__meta-grid {
            grid-template-columns: 1fr 1fr;
        }
        .sales-document__header-right {
            margin-top: 1.25rem;
            text-align: left !important;
        }
        .sales-document__items {
            min-width: 760px;
        }
    }
    @media print {
        .sales-document {
            border: 1px solid #64748b;
            border-radius: 0;
            box-shadow: none;
            overflow: visible;
        }
        .sales-document__header,
        .sales-document__party,
        .sales-document__items thead,
        .sales-document__grand-total th {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .sales-document__items {
            min-width: 0;
            font-size: 9px;
        }
        .sales-document__items td,
        .sales-document__items th {
            padding: .45rem .35rem;
        }
        .sales-document__party,
        .sales-document__totals,
        .sales-document__notes-block,
        .sales-document__signature-block {
            break-inside: avoid;
        }
        .sales-document__header,
        .sales-document__parties {
            break-inside: avoid;
        }
        .sales-document .table-responsive {
            overflow: visible !important;
        }
        .sales-document a {
            color: inherit;
            text-decoration: none;
        }
    }
</style>

<article class="card sales-document sales-document--{{ $type }}">
    <div class="card-body p-4 p-md-5">
        <header class="sales-document__header mb-4">
            <span class="sales-document__diagonal" aria-hidden="true"></span>
            <div class="row align-items-start">
                <div class="col-md-7">
                    <div class="sales-document__company-name">{{ $record->company->name }}</div>
                    <div class="sales-document__company-meta mt-2">
                        @if($record->company->address)<div style="white-space: pre-line">{{ $record->company->address }}</div>@endif
                        @if($record->company->city || $record->company->state || $record->company->pincode)
                            <div>{{ collect([$record->company->city, $record->company->state, $record->company->pincode])->filter()->implode(', ') }}</div>
                        @endif
                        @if($record->company->email || $record->company->phone)
                            <div>{{ collect([$record->company->email, $record->company->phone])->filter()->implode(' · ') }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-md-5 text-md-end sales-document__header-right">
                    <div class="sales-document__type">{{ $documentTypeLabel }}</div>
                    @if($type === 'quotation' && $record->title)
                        <h1 class="sales-document__title">{{ $record->title }}</h1>
                    @else
                        <h1 class="sales-document__title">{{ $documentTypeLabel }}</h1>
                    @endif
                    <div class="sales-document__number">{{ $documentNumber }}</div>
                    <div class="mt-2"><span class="sales-document__status">{{ str_replace('_', ' ', $record->status) }}</span></div>
                    <div class="sales-document__meta-grid">
                        <div><div class="sales-document__meta-label">Date</div><div class="sales-document__meta-value">{{ $record->{$type.'_date'}->format('d M Y') }}</div></div>
                        @foreach(['valid_until' => 'Valid until', 'due_date' => 'Due date', 'start_date' => 'Start date', 'completion_date' => 'Completion date'] as $field => $label)
                            @if($record->$field)
                                <div><div class="sales-document__meta-label">{{ $label }}</div><div class="sales-document__meta-value">{{ $record->$field->format('d M Y') }}</div></div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </header>

        <section class="row g-3 sales-document__parties mb-4">
            <div class="col-md-6">
                <div class="sales-document__party">
                    <div class="sales-document__section-label">Bill to</div>
                    <div class="sales-document__party-name">{{ $record->client->name }}</div>
                    <div class="sales-document__client-meta">
                        @if($record->client->company_name)<div>{{ $record->client->company_name }}</div>@endif
                        @if($record->client->billing_address)<div style="white-space: pre-line">{{ $record->client->billing_address }}</div>@endif
                        @if($record->client->city || $record->client->state || $record->client->zip_code)
                            <div>{{ collect([$record->client->city, $record->client->state, $record->client->zip_code])->filter()->implode(', ') }}</div>
                        @endif
                        @if($record->client->email)<div>{{ $record->client->email }}</div>@endif
                        @if($record->client->mobile)<div>{{ $record->client->mobile }}</div>@endif
                        @if($record->client->gst_no)<div>GSTIN: {{ $record->client->gst_no }}</div>@endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="sales-document__party">
                    <div class="sales-document__section-label">Ship to</div>
                    <div class="sales-document__party-name">{{ $record->client->name }}</div>
                    <div class="sales-document__client-meta">
                        @if($record->client->company_name)<div>{{ $record->client->company_name }}</div>@endif
                        @if($record->client->shipping_address)<div style="white-space: pre-line">{{ $record->client->shipping_address }}</div>@else<div>Same as billing address</div>@endif
                        @if($record->client->city || $record->client->state || $record->client->zip_code)
                            <div>{{ collect([$record->client->city, $record->client->state, $record->client->zip_code])->filter()->implode(', ') }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section class="table-responsive mb-4" aria-label="Document items">
            <table class="table table-bordered sales-document__items">
                <thead><tr><th>#</th><th>Item / description</th><th>Qty</th><th>Rate (INR)</th><th>GST %</th><th>Taxable (INR)</th><th>GST (INR)</th><th>Total (INR)</th></tr></thead>
                <tbody>
                @foreach($record->items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $item->item_name }}</strong>@if($item->description)<div class="text-body-secondary">{{ $item->description }}</div>@endif @if($item->hsn_sac)<small>HSN / SAC: {{ $item->hsn_sac }}</small>@endif</td>
                        <td class="text-end">{{ $item->qty }}</td>
                        <td class="text-end">{{ $item->rate }}<small class="d-block text-body-secondary">GST {{ $item->tax_type }}</small></td>
                        <td class="text-end">{{ $item->tax_rate }}</td>
                        <td class="text-end">{{ $item->taxable_amount }}</td>
                        <td class="text-end">{{ $item->tax_amount }}</td>
                        <td class="text-end fw-semibold">{{ $item->total_amount }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </section>

        <section class="row g-4 mb-4 align-items-start sales-document__summary">
            <div class="col-md-7">
                @if($record->notes)
                    <div class="sales-document__notes-block mb-3">
                        <div class="sales-document__section-label mb-2">Notes</div>
                        <div class="sales-document__notes">{{ $record->notes }}</div>
                    </div>
                @endif
                @if($type !== 'job' && $record->terms)
                    <div class="sales-document__notes-block">
                        <div class="sales-document__section-label mb-2">Terms and conditions</div>
                        <div class="sales-document__notes">{{ $record->terms }}</div>
                    </div>
                @endif
            </div>
            <div class="col-md-5 sales-document__totals">
                @include('web.partials.totals')
            </div>
        </section>

        <footer class="text-end sales-document__signature-block">
            <div class="fw-semibold">For {{ $record->company->name }}</div>
            <div class="sales-document__signature">Authorized signatory</div>
        </footer>
    </div>
</article>
