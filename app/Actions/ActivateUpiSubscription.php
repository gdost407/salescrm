<?php

namespace App\Actions;

use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class ActivateUpiSubscription
{
    public function handle(User $user, SubscriptionPlan $plan, string $cycle, string $quotedAmount, string $checkoutId, string $utr, UploadedFile $screenshot): SubscriptionPayment
    {
        $path = null;

        try {
            return DB::transaction(function () use ($user, $plan, $cycle, $quotedAmount, $checkoutId, $utr, $screenshot, &$path): SubscriptionPayment {
                $company = Company::whereKey($user->company_id)->lockForUpdate()->firstOrFail();
                $plan = SubscriptionPlan::whereKey($plan->id)->lockForUpdate()->firstOrFail();
                if (! $plan->is_active || $plan->currency !== 'INR' || ! in_array($cycle, ['monthly', 'yearly'], true)
                    || $plan->{$cycle.'_price'} !== $quotedAmount || ! preg_match('/^(?:[1-9]\d*\.\d{2}|0\.(?!00)\d{2})$/', $quotedAmount)) {
                    throw ValidationException::withMessages(['plan' => 'The selected plan or price has changed. Please review the plan before submitting payment.']);
                }
                if (SubscriptionPayment::where('company_id', $company->id)->where('payment_order_id', $checkoutId)->exists()) {
                    throw ValidationException::withMessages(['utr' => 'This checkout has already been submitted. Check your subscription payment history.']);
                }
                if (SubscriptionPayment::where('transaction_id', $utr)->exists()) {
                    throw ValidationException::withMessages(['utr' => 'This transaction reference has already been submitted.']);
                }

                $path = $screenshot->store('subscription-payments/'.$company->id, 'local');
                if ($path === false) {
                    throw ValidationException::withMessages(['screenshot' => 'The screenshot could not be saved. Please try again.']);
                }

                $now = now();
                $current = $company->subscriptions()->where('status', 'active')
                    ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
                    ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>', $now))
                    ->latest('starts_at')->lockForUpdate()->first();
                $renewing = $current && $current->plan_id === $plan->id;
                $periodStart = $renewing && $current->ends_at ? $current->ends_at->copy() : $now->copy();
                $periodEnd = $cycle === 'yearly' ? $periodStart->copy()->addYearNoOverflow() : $periodStart->copy()->addMonthNoOverflow();
                $subscription = $renewing ? $current : new CompanySubscription(['company_id' => $company->id, 'plan_id' => $plan->id, 'starts_at' => $now]);

                $company->subscriptions()->where('status', 'active')
                    ->when($renewing, fn ($query) => $query->whereKeyNot($current->id))
                    ->update(['status' => 'cancelled', 'cancelled_at' => $now, 'ends_at' => $now, 'auto_renew' => false]);
                $subscription->fill([
                    'billing_cycle' => $cycle, 'amount' => $quotedAmount, 'currency' => 'INR',
                    'status' => 'active', 'ends_at' => $periodEnd, 'cancelled_at' => null,
                    'auto_renew' => false, 'gateway' => 'manual_upi',
                ])->save();

                return SubscriptionPayment::create([
                    'company_id' => $company->id, 'subscription_id' => $subscription->id, 'plan_id' => $plan->id,
                    'amount' => $quotedAmount, 'currency' => 'INR', 'gateway' => 'manual_upi',
                    'transaction_id' => $utr, 'payment_order_id' => $checkoutId, 'payment_method' => 'upi',
                    'status' => 'pending_verification', 'paid_at' => null,
                    'gateway_response' => [
                        'screenshot_path' => $path, 'submitted_by' => $user->id, 'submitted_at' => $now->toIso8601String(),
                        'billing_cycle' => $cycle, 'period_starts_at' => $periodStart->toIso8601String(),
                        'period_ends_at' => $periodEnd->toIso8601String(), 'upi_id' => config('services.upi.id'),
                    ],
                ]);
            });
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            if ($exception instanceof UniqueConstraintViolationException) {
                throw ValidationException::withMessages(['utr' => 'This transaction reference has already been submitted.']);
            }
            throw $exception;
        }
    }
}
