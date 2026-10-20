@extends('layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
@include('web.partials.alerts')
<style>
    .quotation-timeline {
        position: relative;
    }
    .quotation-timeline::before {
        position: absolute;
        top: .35rem;
        bottom: .75rem;
        left: .38rem;
        width: 1px;
        background: #e5e7eb;
        content: '';
    }
    .quotation-timeline__item {
        position: relative;
        padding-left: 1.5rem;
        padding-bottom: 1.25rem;
    }
    .quotation-timeline__dot {
        position: absolute;
        top: .25rem;
        left: 0;
        width: .8rem;
        height: .8rem;
        border: 2px solid #fff;
        border-radius: 50%;
        background: #696cff;
        box-shadow: 0 0 0 1px #696cff;
    }
    @media print {
        body * {
            visibility: hidden !important;
        }
        .sales-document,
        .sales-document * {
            visibility: visible !important;
        }
        .sales-document {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
        }
    }
</style>
@php
    $whatsAppNumber = preg_replace('/\\D+/', '', (string) $record->client->mobile);
    $whatsAppMessage = collect([
        'Hello '.$record->client->name.',',
        'Please find quotation '.($record->quotation_no ?? '').($record->title ? ' - '.$record->title : '').'.',
        'Total: ₹ '.number_format((float) $record->total_amount, 2),
        $record->valid_until ? 'Valid until: '.$record->valid_until->format('d M Y') : null,
    ])->filter()->implode("\n");
@endphp
<div class="d-flex justify-content-end gap-2 mb-3 d-print-none">
    @if(auth()->user()->hasPermission('edit_quotations'))
        <a class="btn btn-outline-secondary" href="{{ route('quotations.edit', $record->id) }}" title="Edit quotation" aria-label="Edit quotation"><i class="bx bx-edit-alt"></i></a>
    @endif
    <button type="button" class="btn btn-outline-primary" onclick="window.print()" title="Print quotation" aria-label="Print quotation"><i class="bx bx-printer"></i></button>
    @if($whatsAppNumber !== '')
        <a class="btn btn-outline-success" target="_blank" rel="noopener noreferrer" href="https://wa.me/{{ $whatsAppNumber }}?text={{ rawurlencode($whatsAppMessage) }}" title="Send quotation details on WhatsApp" aria-label="Send quotation details on WhatsApp"><i class="bx bxl-whatsapp"></i></a>
    @endif
    @if(auth()->user()->hasPermission('delete_quotations'))
        <form method="POST" action="{{ route('quotations.destroy', $record->id) }}" onsubmit="return confirm('Delete this quotation?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline-danger" title="Delete quotation" aria-label="Delete quotation"><i class="bx bx-trash"></i></button>
        </form>
    @endif
</div>
<div class="row g-4 align-items-start">
    <div class="col-lg-9">
        @include('web.partials.document-details')
        @include('web.partials.payment-summary')
    </div>
    <aside class="col-lg-3 d-print-none" aria-label="Quotation activity">
        @include('web.partials.attachments')
        <div class="card mb-4">
            <div class="card-header pb-2">
                <h5 class="card-title mb-1">Activity log</h5>
                <div class="small text-body-secondary">Current status</div>
                <span class="badge bg-label-primary mt-2">{{ ucfirst(str_replace('_', ' ', $record->status)) }}</span>
            </div>
            <div class="card-body pt-3">
                <div class="quotation-timeline">
                    @forelse(collect($record->activity_log ?? [])->reverse() as $activity)
                        @php($occurredAt = !empty($activity['occurred_at']) ? \Illuminate\Support\Carbon::parse($activity['occurred_at']) : null)
                        <div class="quotation-timeline__item {{ $loop->last ? 'pb-0' : '' }}">
                            <span class="quotation-timeline__dot {{ ($activity['action'] ?? '') === 'status_changed' ? 'bg-info' : '' }}"></span>
                            <div class="fw-semibold">{{ $activity['summary'] ?? 'Quotation updated' }}</div>
                            <div class="small text-body-secondary">{{ $activity['user_name'] ?? 'System' }}</div>
                            @if($occurredAt)
                                <time class="small text-body-secondary" datetime="{{ $occurredAt->toIso8601String() }}">{{ $occurredAt->format('d M Y, h:i A') }}</time>
                            @endif
                            @foreach(($activity['changes'] ?? []) as $change)
                                <div class="small mt-1">
                                    <span class="text-body-secondary">{{ $change['label'] ?? 'Updated' }}:</span>
                                    {{ $change['from'] ?? '—' }} <i class="bx bx-right-arrow-alt"></i> {{ $change['to'] ?? '—' }}
                                </div>
                            @endforeach
                        </div>
                    @empty
                        <div class="quotation-timeline__item pb-0">
                            <span class="quotation-timeline__dot"></span>
                            <div class="fw-semibold">Quotation created</div>
                            <div class="small text-body-secondary">{{ $record->creator?->name ?? 'System' }}</div>
                            <time class="small text-body-secondary" datetime="{{ $record->created_at?->toIso8601String() }}">{{ $record->created_at?->format('d M Y, h:i A') }}</time>
                        </div>
                    @endforelse
                </div>
                @if(empty($record->activity_log))
                    <p class="small text-body-secondary mb-0 mt-2">Future quotation changes will appear here.</p>
                @endif
            </div>
        </div>
    </aside>
</div>
</div>
@endsection
