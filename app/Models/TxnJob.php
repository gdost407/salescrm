<?php

namespace App\Models;

use Database\Factories\TxnJobFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class TxnJob extends Model
{
    /** @use HasFactory<TxnJobFactory> */
    use HasFactory;

    protected $fillable = [
        'company_id', 'client_id', 'quotation_id', 'job_no', 'job_date', 'start_date', 'completion_date', 'subtotal', 'discount_amount', 'tax_amount', 'total_amount', 'status', 'notes', 'created_by',
    ];

    protected $attributes = [
        'subtotal' => '0.00',
        'discount_amount' => '0.00',
        'tax_amount' => '0.00',
        'total_amount' => '0.00',
        'status' => 'draft',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'job_date' => 'date',
            'start_date' => 'date',
            'completion_date' => 'date',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): MorphMany
    {
        return $this->morphMany(TxnHistoryItem::class, 'document')->orderBy('sort_order')->orderBy('id');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(TxnAttachment::class, 'document');
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(TxnPayment::class, 'document');
    }

    public function ledgerEntries(): MorphMany
    {
        return $this->morphMany(TxnLedger::class, 'document');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(TxnQuotation::class, 'quotation_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(TxnInvoice::class, 'job_id');
    }
}
