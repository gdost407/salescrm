@if(auth()->user()->hasPermission('view_payments') && $payments->isNotEmpty())
<div class="d-print-none mt-4"><h5>Recorded payments</h5>
<p class="text-muted">These independent records do not update the stored invoice balance.</p>
<table class="table"><thead><tr><th>Payment</th><th>Date</th><th>Mode</th><th class="text-end">Amount (INR)</th></tr></thead><tbody>
@foreach($payments as $payment)
<tr><td><a href="{{ route('payments.show', $payment) }}">{{ $payment->payment_no }}</a></td><td>{{ $payment->payment_date->format('d M Y') }}</td><td>{{ ucfirst($payment->payment_mode) }}</td><td class="text-end">{{ $payment->amount }}</td></tr>
@endforeach
</tbody></table></div>
@endif
