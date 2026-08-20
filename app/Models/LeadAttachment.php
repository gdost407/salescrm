<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'lead_id', 'uploaded_by', 'original_name', 'file_name',
        'file_path', 'disk', 'mime_type', 'file_size', 'file_extension', 'description',
    ];

    protected function casts(): array
    {
        return ['file_size' => 'integer'];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
