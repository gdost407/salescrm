<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Integration extends Model
{
    use HasFactory;

    protected $hidden = [
        'api_key',
        'api_secret',
        'webhook_token',
    ];

    protected $fillable = [
        'company_id', 'name', 'type', 'api_key', 'api_secret', 'webhook_token',
        'configuration', 'status',
    ];

    protected function casts(): array
    {
        return ['configuration' => 'array', 'status' => 'boolean'];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function webhookLogs(): HasMany
    {
        return $this->hasMany(WebhookLog::class);
    }
}
