<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'email', 'phone', 'website', 'address', 'city',
        'state', 'country', 'pincode', 'logo', 'staff_limit', 'status',
        'onboarding_completed_at',
    ];

    protected function casts(): array
    {
        return [
            'staff_limit' => 'integer',
            'onboarding_completed_at' => 'datetime',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function trialEndsAt(): ?Carbon
    {
        return $this->onboarding_completed_at?->copy()->addMonthNoOverflow();
    }

    public function hasActiveTrial(): bool
    {
        return $this->onboarding_completed_at !== null
            && $this->onboarding_completed_at->lte(now())
            && $this->trialEndsAt()->gt(now());
    }

    public function hasWorkspaceAccess(): bool
    {
        return $this->hasActiveTrial() || $this->subscriptions()
            ->where('status', 'active')
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>', now()))
            ->exists();
    }

    public function leadSettings(): HasMany
    {
        return $this->hasMany(LeadSetting::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function integrations(): HasMany
    {
        return $this->hasMany(Integration::class);
    }

    public function webhookLogs(): HasMany
    {
        return $this->hasMany(WebhookLog::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(CompanySubscription::class);
    }

    public function subscriptionPayments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function leadActivities(): HasMany
    {
        return $this->hasMany(LeadActivity::class);
    }

    public function leadAttachments(): HasMany
    {
        return $this->hasMany(LeadAttachment::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
