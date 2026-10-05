@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <a href="{{ route('subscription.index') }}" class="btn btn-outline-secondary mb-4"><i class="bx bx-arrow-back me-2" aria-hidden="true"></i>Back to plans</a>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div><h3 class="mb-1">Subscription checkout</h3><p class="text-body-secondary mb-0">Review your plan and payment details.</p></div>
        <span class="badge bg-label-primary"><i class="bx bx-wallet me-1" aria-hidden="true"></i>Pay with UPI</span>
    </div>
    @include('web.partials.alerts')
    @unless ($checkoutReady)<div class="alert alert-warning" role="alert">UPI checkout is not available for this plan yet. Please do not make a payment until payment submission is enabled.</div>@endunless
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header"><h5 class="mb-0">Your selected plan</h5></div>
                <div class="card-body">
                    <span class="avatar mb-4"><span class="avatar-initial rounded bg-label-primary"><i class="bx bx-group" aria-hidden="true"></i></span></span>
                    <h4>{{ $plan->name }}</h4>
                    <p class="text-body-secondary">{{ $plan->description }}</p>
                    <dl class="row mb-4">
                        <dt class="col-7 fw-normal text-body-secondary">Billing cycle</dt><dd class="col-5 text-end">{{ ucfirst($cycle) }}</dd>
                        <dt class="col-7 fw-normal text-body-secondary">Staff capacity</dt><dd class="col-5 text-end">{{ $plan->max_users }}</dd>
                        <dt class="col-7 fw-normal text-body-secondary">Duration</dt><dd class="col-5 text-end">{{ $cycle === 'yearly' ? '1 year' : '1 month' }}</dd>
                    </dl>
                    <div class="border-top pt-4">
                        <p class="text-body-secondary mb-1">Plan price</p>
                        <h3 class="mb-0">{{ $plan->currency }} {{ $amount }}</h3>
                        @if ($paymentAmount !== null)
                            <p class="mt-2 mb-0">Total payable: <strong>INR {{ $paymentAmount }}</strong></p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">1. Payment details</h5></div>
                <div class="card-body">
                    @if ($paymentAmount === null)
                        <p class="text-body-secondary mb-0">UPI pricing is not available for this plan yet.</p>
                    @elseif ($checkoutReady)
                        <div class="row g-4 align-items-center">
                            <div class="col-sm-5 text-center">
                                <div id="upi-qr" class="d-inline-block mw-100 rounded border p-4 bg-white" data-upi-url="{{ $upiUrl }}" role="img" aria-label="UPI payment QR code for {{ $plan->name }}, INR {{ $paymentAmount }}"></div>
                                <p id="upi-qr-error" class="text-danger small mt-2 d-none" role="alert">The QR code could not be generated. Use the UPI ID and exact amount shown to make your payment.</p>
                                <noscript><p class="text-body-secondary small mt-2">Enable JavaScript to display the QR code, or pay using the UPI ID and amount shown.</p></noscript>
                            </div>
                            <div class="col-sm-7">
                                <p class="text-body-secondary mb-1">Payee</p><h6>{{ $payeeName }}</h6>
                                <p class="text-body-secondary mb-1">UPI ID</p><p class="fw-semibold text-break">{{ $upiId }}</p>
                                <p class="mb-0">Scan the QR code and pay exactly <strong>INR {{ $paymentAmount }}</strong>. Keep your transaction reference and screenshot.</p>
                            </div>
                        </div>
                    @else
                        <p class="text-body-secondary mb-0">Payment details will appear here when UPI checkout is available.</p>
                    @endif
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h5 class="mb-0">2. Submit payment proof</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ $submitUrl ?? '#' }}" enctype="multipart/form-data">
                    @csrf
                    <fieldset @disabled(! $checkoutReady)>
                        <div class="mb-4">
                            <label class="form-label" for="utr">UTR / UPI transaction number</label>
                            <div class="input-group input-group-merge"><span class="input-group-text text-danger" aria-hidden="true"><i class="bx bx-receipt"></i></span><input type="text" id="utr" name="utr" class="form-control @error('utr') is-invalid @enderror" value="{{ old('utr') }}" minlength="6" maxlength="64" pattern="[A-Za-z0-9]{6,64}" placeholder="Enter the transaction reference from your UPI app" required @error('utr') aria-invalid="true" aria-describedby="utr-error" @enderror></div>
                            @error('utr')<div id="utr-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="screenshot">Payment screenshot</label>
                            <div class="input-group input-group-merge"><span class="input-group-text text-danger" aria-hidden="true"><i class="bx bx-image"></i></span><input type="file" id="screenshot" name="screenshot" class="form-control" accept="image/jpeg,image/png,image/webp" required aria-describedby="screenshot-help"></div>
                            <small id="screenshot-help" class="text-body-secondary">JPG, PNG or WebP, up to 5 MB, showing the amount and transaction reference. After a validation error, select the file again.</small>
                            @error('screenshot')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <p class="text-body-secondary">Your subscription activates immediately after submission. Payment verification is handled separately. Renewing the same plan extends its expiry; changing plans starts a new period immediately without proration.</p>
                        <button type="submit" class="btn btn-primary"><i class="bx bx-check-circle me-2" aria-hidden="true"></i>Submit payment &amp; activate subscription</button>
                    </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@if ($checkoutReady)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('upi-qr');
    if (!container) return;
    try {
        new QRCode(container, {
            text: container.dataset.upiUrl,
            width: 220,
            height: 220,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M,
        });
        container.querySelectorAll('canvas, img').forEach((element) => element.classList.add('img-fluid'));
    } catch (error) {
        container.replaceChildren();
        container.classList.add('d-none');
        document.getElementById('upi-qr-error').classList.remove('d-none');
    }
});
</script>
@endpush
@endif
