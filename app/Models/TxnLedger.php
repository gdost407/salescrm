<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TxnLedger extends Model
{
    /** @use HasFactory<\Database\Factories\TxnLedgerFactory> */
    use HasFactory;

    protected $table = 'txn_ledger';

    protected $fillable = [
        'company_id', 'ledger_type', 'client_id', 'transaction_date', 'entry_type', 'transaction_type', 'document_type', 'document_id', 'amount', 'reference_no', 'description', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function document(): MorphTo
    {
        return $this->morphTo();
    }
}
