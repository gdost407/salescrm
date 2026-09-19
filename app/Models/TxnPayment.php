<?php

namespace App\Models;

use Database\Factories\TxnPaymentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TxnPayment extends Model
{
    /** @use HasFactory<TxnPaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'company_id', 'client_id', 'payment_no', 'document_type', 'document_id', 'payment_date', 'amount', 'payment_mode', 'reference_no', 'notes', 'received_by',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where($this->qualifyColumn('company_id'), $companyId);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function document(): MorphTo
    {
        return $this->morphTo();
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
