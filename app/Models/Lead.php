<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'name', 'email', 'mobile', 'alternate_mobile', 'job_title',
        'deal_amount', 'company_name', 'status', 'stage', 'source', 'created_by',
        'assigned_to', 'client_id', 'converted_at', 'address', 'city', 'state',
        'country', 'pincode', 'description', 'notes', 'priority',
        'last_contacted_at', 'last_activity_at',
    ];

    protected function casts(): array
    {
        return [
            'deal_amount' => 'decimal:2',
            'converted_at' => 'datetime',
            'last_contacted_at' => 'datetime',
            'last_activity_at' => 'datetime',
        ];
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $query->where('company_id', $user->company_id);

        if ($user->hasPermission('view_all_leads')) {
            return $query;
        }

        return $user->hasPermission('view_own_leads')
            ? $query->where('assigned_to', $user->id)
            : $query->whereRaw('1 = 0');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(LeadAttachment::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
