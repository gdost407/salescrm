<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'client_code', 'type', 'name', 'company_name', 'email', 'mobile',
        'gst_no', 'pan_no', 'billing_address', 'shipping_address', 'country', 'state',
        'city', 'zip_code', 'notes', 'created_by', 'is_active',
    ];

    protected $attributes = ['type' => 'business', 'is_active' => true];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(TxnQuotation::class);
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(TxnJob::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(TxnInvoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(TxnPayment::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(TxnLedger::class);
    }
}
