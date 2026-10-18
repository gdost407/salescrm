@extends('layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
@include('web.partials.alerts')
<style>
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
        'Total: INR '.number_format((float) $record->total_amount, 2),
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
@include('web.partials.document-details')
@include('web.partials.attachments')
@include('web.partials.payment-summary')
</div>
@endsection
