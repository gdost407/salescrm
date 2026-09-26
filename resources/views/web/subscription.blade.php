@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @include('web.partials.alerts')
    <div class="card mb-6">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <span class="badge bg-label-primary mb-3"><i class="bx bx-credit-card me-1" aria-hidden="true"></i>Subscription &amp; Billing</span>
                    <h3 class="mb-2">A plan for your growing team</h3>
                    <p class="text-body-secondary mb-0">Compare plans, review your subscription, and keep track of payments.</p>
                </div>
                <a href="#plans" class="btn btn-outline-primary">Explore plans<i class="bx bx-right-arrow-alt ms-2" aria-hidden="true"></i></a>
            </div>
            <div class="row g-4 pt-3 border-top">
                <div class="col-sm-6 col-lg-3">
                    <small class="text-body-secondary d-block mb-1">Current plan</small>
                    <h5 class="mb-1">{{ $subscription?->plan?->name ?? 'No active subscription' }}</h5>
                    <span class="badge {{ $subscription ? 'bg-label-success' : 'bg-label-secondary' }}">{{ $subscription ? 'Active' : 'Not subscribed' }}</span>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <small class="text-body-secondary d-block mb-1">Staff capacity</small>
                    <h5 class="mb-0">{{ $staffCount }} / {{ $subscription?->plan?->max_users ?? $company->staff_limit }}</h5>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <small class="text-body-secondary d-block mb-1">Billing cycle</small>
                    <h5 class="mb-0">{{ $subscription ? ucfirst($subscription->billing_cycle) : '—' }}</h5>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <small class="text-body-secondary d-block mb-1">Current period ends</small>
                    <h5 class="mb-0">{{ $subscription?->ends_at?->format('d M Y') ?? '—' }}</h5>
                </div>
            </div>
        </div>
    </div>

    <div id="plans" class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h4 class="mb-1">Choose your plan</h4><p class="text-body-secondary mb-0">Compare the features and capacity your team needs.</p></div>
        <div class="btn-group" role="group" aria-label="Billing cycle">
            <button type="button" class="btn btn-primary billing-cycle" data-cycle="monthly" aria-pressed="true">Monthly</button>
            <button type="button" class="btn btn-outline-primary billing-cycle" data-cycle="yearly" aria-pressed="false">Yearly</button>
        </div>
    </div>
    <div class="row g-4 mb-6">
        @forelse ($plans as $plan)
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 {{ $subscription?->plan_id === $plan->id ? 'border border-primary' : '' }}">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="avatar"><span class="avatar-initial rounded bg-label-primary"><i class="bx bx-group" aria-hidden="true"></i></span></span>
                            @if ($subscription?->plan_id === $plan->id)<span class="badge bg-label-primary">Current plan</span>@endif
                        </div>
                        <h4>{{ $plan->name }}</h4>
                        <p class="text-body-secondary">{{ $plan->description }}</p>
                        <div class="mb-4" aria-live="polite">
                            <span class="small text-body-secondary">{{ $plan->currency }}</span>
                            <span class="h2 plan-price" data-monthly="{{ $plan->monthly_price }}" data-yearly="{{ $plan->yearly_price }}">{{ $plan->monthly_price }}</span>
                            <span class="text-body-secondary plan-period">/ month</span>
                        </div>
                        <p class="fw-semibold"><i class="bx bx-user me-2 text-primary" aria-hidden="true"></i>Up to {{ $plan->max_users }} staff members</p>
                        <ul class="list-unstyled border-top pt-4 mb-4">
                            @foreach ($plan->features as $feature)
                                <li class="d-flex gap-2 mb-3">
                                    @if ($feature->type === 'boolean' && ! in_array(strtolower((string) $feature->pivot->value), ['1', 'true', 'yes'], true))
                                        <i class="bx bx-minus text-body-secondary" aria-hidden="true"></i><span class="text-body-secondary">{{ $feature->name }} (not included)</span>
                                    @else
                                        <i class="bx bx-check text-success" aria-hidden="true"></i><span>{{ $feature->name }}@if ($feature->type !== 'boolean'): {{ $feature->pivot->value }}@endif</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn btn-outline-primary w-100 mt-auto" disabled>Checkout coming soon</button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12"><div class="card"><div class="card-body text-center py-5"><i class="bx bx-package fs-1 text-body-secondary" aria-hidden="true"></i><h5 class="mt-3">No plans available yet</h5><p class="mb-0 text-body-secondary">Subscription plans will appear here when they are available.</p></div></div></div>
        @endforelse
    </div>

    <div class="card">
        <div class="card-header"><h5 class="mb-1">Payment history</h5><p class="text-body-secondary mb-0">Your company’s subscription payments in one place.</p></div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th>Date</th><th>Plan</th><th>Amount</th><th>Status</th><th>Reference</th></tr></thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td class="text-nowrap">{{ ($payment->paid_at ?? $payment->created_at)->format('d M Y') }}</td>
                            <td>{{ $payment->plan?->name }}</td>
                            <td class="text-nowrap">{{ $payment->currency }} {{ $payment->amount }}</td>
                            <td><span class="badge {{ $payment->status === 'paid' ? 'bg-label-success' : ($payment->status === 'failed' ? 'bg-label-danger' : 'bg-label-secondary') }}">{{ ucfirst($payment->status) }}</span></td>
                            <td>{{ $payment->transaction_id ?? $payment->payment_order_id ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-5 text-body-secondary">No payments yet. Your subscription payment history will appear here.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($payments->hasPages())<div class="card-body">{{ $payments->links() }}</div>@endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    $('.billing-cycle').on('click', function () {
        const cycle = this.dataset.cycle;
        $('.billing-cycle').removeClass('btn-primary').addClass('btn-outline-primary').attr('aria-pressed', 'false');
        $(this).addClass('btn-primary').removeClass('btn-outline-primary').attr('aria-pressed', 'true');
        $('.plan-price').each(function () { $(this).text(this.dataset[cycle]); });
        $('.plan-period').text(cycle === 'yearly' ? '/ year' : '/ month');
    });
});
</script>
@endpush
