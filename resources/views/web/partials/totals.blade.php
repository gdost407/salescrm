<div class="row justify-content-end"><div class="col-md-5">
<table class="table">
<tr><th>Subtotal (INR)</th><td class="text-end" @if($editing ?? false) id="quotation-subtotal" @endif>{{ $record->subtotal ?? '0.00' }}</td></tr>
@if(!($editing ?? false) && $record->discount_amount !== '0.00')
<tr><th>Discount</th><td class="text-end">{{ $record->discount_amount }}</td></tr>
@endif
<tr><th>GST / Tax (INR)</th><td class="text-end" @if($editing ?? false) id="quotation-tax" @endif>{{ $record->tax_amount ?? '0.00' }}</td></tr>
<tr class="table-light"><th>Total (INR)</th><th class="text-end" @if($editing ?? false) id="quotation-total" @endif>{{ $record->total_amount ?? '0.00' }}</th></tr>
@if($type === 'invoice' && !($editing ?? false))
<tr><th>Stored paid amount</th><td class="text-end">{{ $record->paid_amount }}</td></tr>
<tr><th>Stored balance</th><td class="text-end">{{ $record->balance_amount }}</td></tr>
@endif
</table>
</div></div>
