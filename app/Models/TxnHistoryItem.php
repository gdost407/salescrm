<?php

namespace App\Models;

use Database\Factories\TxnHistoryItemFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TxnHistoryItem extends Model
{
    /** @use HasFactory<TxnHistoryItemFactory> */
    use HasFactory;

    protected $fillable = [
        'company_id', 'document_type', 'document_id', 'item_id', 'item_type', 'item_name', 'description', 'hsn_sac', 'sku', 'qty', 'rate', 'tax_id', 'tax_rate', 'tax_type', 'taxable_amount', 'tax_amount', 'total_amount', 'sort_order',
    ];

    protected $attributes = [
        'qty' => '1.000',
        'tax_rate' => '0.0000',
        'tax_type' => 'exclusive',
        'taxable_amount' => '0.00',
        'tax_amount' => '0.00',
        'total_amount' => '0.00',
        'sort_order' => 0,
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:3',
            'rate' => 'decimal:2',
            'tax_rate' => 'decimal:4',
            'taxable_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'sort_order' => 'integer',
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

    public function document(): MorphTo
    {
        return $this->morphTo();
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }
}
