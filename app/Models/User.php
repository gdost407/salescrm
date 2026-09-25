<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable // implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** @var array<string, mixed> */
    protected $attributes = ['is_active' => true];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'user_type',
        'role_id',
        'name',
        'email',
        'mobile',
        'joining_date',
        'department_id',
        'department',
        'job_role',
        'address',
        'country',
        'state',
        'city',
        'zip_code',
        'working_time',
        'salary_type',
        'salary',
        'is_active',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'joining_date' => 'date',
            'salary' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function createdLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'created_by');
    }

    public function assignedLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'created_by');
    }

    public function leadActivities(): HasMany
    {
        return $this->hasMany(LeadActivity::class);
    }

    public function uploadedAttachments(): HasMany
    {
        return $this->hasMany(LeadAttachment::class, 'uploaded_by');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function hasRole(string $slug): bool
    {
        return $this->role?->slug === $slug;
    }

    public function canViewAllAttendance(): bool
    {
        return $this->is_active && $this->company_id && ($this->user_type === 'owner'
            || ($this->role?->slug === 'admin' && $this->role->status
                && (int) $this->role->company_id === (int) $this->company_id));
    }

    public function hasPermission(string $permission): bool
    {
        if (! $this->is_active || ! $this->company_id || ! array_key_exists($permission, array_merge(...array_values(Permission::MODULES)))) {
            return false;
        }

        if ($this->user_type === 'owner') {
            return true;
        }

        $this->loadMissing('role.permissions');
        $role = $this->role;

        if (! $this->is_active || ! $role?->status || (int) $role->company_id !== (int) $this->company_id) {
            return false;
        }

        $permissionSlugs = [$permission];
        if (str_contains($permission, '_own_')) {
            $permissionSlugs[] = str_replace('_own_', '_all_', $permission);
        }

        return $role->permissions
            ->where('company_id', $this->company_id)
            ->where('status', true)
            ->whereIn('slug', $permissionSlugs)
            ->isNotEmpty();
    }

    public function canManageStaffAccount(User $staff, string $action = 'edit'): bool
    {
        return (int) $staff->company_id === (int) $this->company_id
            && $staff->user_type === 'staff'
            && in_array($action, ['view', 'edit', 'delete'], true)
            && $this->hasPermission($action.'_staff');
    }

    public function canAccessLeadActivity(Lead $lead, string $action, string $type, ?LeadActivity $activity = null): bool
    {
        if (! $this->canAccessLead($lead) || ($activity && ((int) $activity->company_id !== (int) $lead->company_id || (int) $activity->lead_id !== (int) $lead->id))) {
            return false;
        }

        $scheduled = in_array($type, ['followup', 'visit', 'gmeet'], true);
        $permission = match ($action) {
            'create' => $scheduled ? 'schedule_%s_followups' : 'create_%s_activities',
            'edit' => $scheduled ? 'manage_%s_followups' : 'edit_%s_activities',
            'delete' => 'delete_%s_activities',
            'complete' => $scheduled ? 'work_%s_followups' : null,
            default => null,
        };

        if ($permission === null) {
            return false;
        }

        return $this->hasPermission(sprintf($permission, 'all'))
            || (((int) $lead->assigned_to === (int) $this->id || ($activity && (int) $activity->user_id === (int) $this->id))
                && $this->hasPermission(sprintf($permission, 'own')));
    }

    public function canAccessLead(Lead $lead, string $action = 'view'): bool
    {
        if ((int) $lead->company_id !== (int) $this->company_id || ! in_array($action, ['view', 'edit', 'delete'], true)) {
            return false;
        }

        return $this->hasPermission($action.'_all_leads')
            || ((int) $lead->assigned_to === (int) $this->id && $this->hasPermission($action.'_own_leads'));
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }
}
