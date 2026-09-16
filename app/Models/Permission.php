<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Permission extends Model
{
    use HasFactory;

    public const MODULES = [
        'lead' => [
            'view_own_leads' => 'View own leads',
            'view_all_leads' => 'View all leads',
            'create_leads' => 'Create leads',
            'edit_own_leads' => 'Edit own leads',
            'edit_all_leads' => 'Edit all leads',
            'delete_own_leads' => 'Delete own leads',
            'delete_all_leads' => 'Delete all leads',
        ],
        'staff' => [
            'view_staff' => 'View staff',
            'create_staff' => 'Create staff',
            'edit_staff' => 'Edit staff',
            'delete_staff' => 'Delete staff',
        ],
    ];

    protected $fillable = ['company_id', 'name', 'slug', 'module', 'description', 'status'];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }

    public function rolePermissions(): HasMany
    {
        return $this->hasMany(RolePermission::class);
    }
}
