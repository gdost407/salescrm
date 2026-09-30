@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center"><div class="col-lg-8">
        <div class="card">
            <div class="card-body p-4 p-lg-5">
                <div class="text-center mb-5">
                    <span class="avatar avatar-xl mx-auto mb-4"><span class="avatar-initial rounded-circle bg-label-success"><i class="bx bx-check-circle fs-1" aria-hidden="true"></i></span></span>
                    <h3>{{ $payment->subscription->status === 'active' ? 'Your subscription is active!' : 'Payment submission received' }}</h3>
                    <p class="text-body-secondary mb-0">Your payment proof has been saved. Payment verification is separate from your subscription status.</p>
                </div>
                <dl class="row g-3">
                    <dt class="col-sm-5 text-body-secondary fw-normal">Plan</dt><dd class="col-sm-7">{{ $payment->plan->name }}</dd>
                    <dt class="col-sm-5 text-body-secondary fw-normal">Subscription status</dt><dd class="col-sm-7"><span class="badge bg-label-primary">{{ ucfirst($payment->subscription->status) }}</span></dd>
                    <dt class="col-sm-5 text-body-secondary fw-normal">Payment verification</dt><dd class="col-sm-7"><span class="badge bg-label-warning">{{ ucfirst(str_replace('_', ' ', $payment->status)) }}</span></dd>
                    <dt class="col-sm-5 text-body-secondary fw-normal">Amount submitted</dt><dd class="col-sm-7">{{ $payment->currency }} {{ $payment->amount }}</dd>
                    <dt class="col-sm-5 text-body-secondary fw-normal">UTR / UPI transaction number</dt><dd class="col-sm-7 text-break">{{ $payment->transaction_id }}</dd>
                    <dt class="col-sm-5 text-body-secondary fw-normal">Subscription valid until</dt><dd class="col-sm-7">{{ $payment->subscription->ends_at?->format('d M Y') ?? '—' }}</dd>
                </dl>
                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a href="{{ route('subscription.index') }}" class="btn btn-primary">View subscription</a>
                    <a href="{{ route('subscription.screenshot', $payment) }}" class="btn btn-outline-secondary" target="_blank" rel="noopener">View payment screenshot</a>
                </div>
            </div>
        </div>
    </div></div>
</div>
@endsection
