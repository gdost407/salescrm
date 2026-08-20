<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'max_users', 'monthly_price', 'yearly_price',
        'currency', 'monthly_gateway_plan_id', 'yearly_gateway_plan_id',
        'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'max_users' => 'integer',
            'monthly_price' => 'decimal:2',
            'yearly_price' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(SubscriptionFeature::class, 'plan_features', 'plan_id', 'feature_id')
            ->withPivot('value')
            ->withTimestamps();
    }

    public function planFeatures(): HasMany
    {
        return $this->hasMany(PlanFeature::class, 'plan_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(CompanySubscription::class, 'plan_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class, 'plan_id');
    }
}
