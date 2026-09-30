<?php

namespace App\Http\Controllers\Web;

use App\Actions\ActivateUpiSubscription;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitSubscriptionPaymentRequest;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubscriptionController extends Controller
{
    public function checkout(Request $request, SubscriptionPlan $plan): View
    {
        $user = $request->user();
        abort_unless($user->is_active && $user->user_type === 'owner' && $user->company_id, 403);
        abort_unless($plan->is_active, 404);
        $validated = $request->validate(['cycle' => ['sometimes', 'required', 'in:monthly,yearly']]);
        $cycle = $validated['cycle'] ?? 'monthly';
        $amount = $plan->{$cycle.'_price'};
        $ready = $this->upiConfigured() && $plan->currency === 'INR' && preg_match('/^(?:[1-9]\d*\.\d{2}|0\.(?!00)\d{2})$/', $amount);
        $upiUrl = $ready ? 'upi://pay?'.http_build_query([
            'pa' => config('services.upi.id'),
            'pn' => config('services.upi.payee_name'),
            'am' => $amount,
            'cu' => 'INR',
            'tn' => 'OneCRM '.$plan->name.' Subscription',
        ], '', '&', PHP_QUERY_RFC3986) : null;

        return view('web.subscription-checkout', [
            'plan' => $plan,
            'cycle' => $cycle,
            'amount' => $amount,
            'upiId' => config('services.upi.id'),
            'payeeName' => config('services.upi.payee_name'),
            'upiUrl' => $upiUrl,
            'checkoutReady' => $ready,
            'submitUrl' => $ready ? URL::temporarySignedRoute('subscription.submit', now()->addDay(), [
                'plan' => $plan->id, 'cycle' => $cycle, 'quote_amount' => $amount,
                'company' => $user->company_id, 'user' => $user->id, 'checkout' => 'upi_'.Str::uuid(),
            ]) : null,
        ]);
    }

    public function submit(SubmitSubscriptionPaymentRequest $request, SubscriptionPlan $plan, ActivateUpiSubscription $activate): RedirectResponse
    {
        if (! $this->upiConfigured()) {
            throw ValidationException::withMessages(['payment' => 'UPI checkout is currently unavailable. Please try again later.']);
        }

        $payment = $activate->handle(
            $request->user(), $plan, $request->query('cycle'), $request->query('quote_amount'),
            $request->query('checkout'), $request->validated('utr'), $request->file('screenshot'),
        );

        return to_route('subscription.success', $payment)->with('success', 'Your subscription is active. Payment verification is pending.');
    }

    public function success(Request $request, SubscriptionPayment $payment): View
    {
        $this->authorizePayment($request, $payment);

        return view('web.subscription-success', ['payment' => $payment->load(['plan', 'subscription'])]);
    }

    public function screenshot(Request $request, SubscriptionPayment $payment): StreamedResponse
    {
        $this->authorizePayment($request, $payment);
        $path = data_get($payment->gateway_response, 'screenshot_path');
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, null, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }

    private function authorizePayment(Request $request, SubscriptionPayment $payment): void
    {
        $user = $request->user();
        abort_unless($user->is_active && $user->user_type === 'owner' && $user->company_id, 403);
        abort_unless((int) $payment->company_id === (int) $user->company_id, 404);
    }

    private function upiConfigured(): bool
    {
        return filled(config('services.upi.id')) && filled(config('services.upi.payee_name'));
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->is_active && $user->user_type === 'owner' && $user->company_id, 403);
        $company = $user->company;

        return view('web.subscription', [
            'company' => $company,
            'plans' => SubscriptionPlan::query()->where('is_active', true)
                ->with(['features' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')])
                ->orderBy('sort_order')->get(),
            'subscription' => $company->subscriptions()->with('plan')
                ->where('status', 'active')
                ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>', now()))
                ->latest('starts_at')->first(),
            'payments' => $company->subscriptionPayments()->with('plan')->latest()->paginate(10),
            'staffCount' => $company->users()->where('user_type', 'staff')->count(),
        ]);
    }
}
