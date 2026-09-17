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
            'create_leads' => 'Create',
            'view_own_leads' => 'View self',
            'edit_own_leads' => 'Edit self',
            'delete_own_leads' => 'Delete self',
            'view_all_leads' => 'View all',
            'edit_all_leads' => 'Edit all',
            'delete_all_leads' => 'Delete all',
        ],
        'staff' => [
            'create_staff' => 'Create',
            'view_staff' => 'View',
            'edit_staff' => 'Edit',
            'delete_staff' => 'Delete',
        ],
        'lead_activity' => [
            'create_own_activities' => 'Add activity — self/assigned',
            'edit_own_activities' => 'Update activity — self/assigned',
            'delete_own_activities' => 'Delete activity — self/assigned',
            'create_all_activities' => 'Add activity — all',
            'edit_all_activities' => 'Update activity — all',
            'delete_all_activities' => 'Delete activity — all',
            'schedule_own_followups' => 'Schedule follow-up — self/assigned',
            'manage_own_followups' => 'Manage follow-up — self/assigned',
            'work_own_followups' => 'Work / complete follow-up — self/assigned',
            'schedule_all_followups' => 'Schedule follow-up — all',
            'manage_all_followups' => 'Manage follow-up — all',
            'work_all_followups' => 'Work / complete follow-up — all',
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
