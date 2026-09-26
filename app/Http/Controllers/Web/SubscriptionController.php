<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
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
