<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $aliases = [
            'view_leads' => ['view_own_leads', 'lead', 'View self'],
            'view_staff' => ['view_all_staff', 'staff', 'View all'],
            'edit_staff' => ['edit_all_staff', 'staff', 'Edit all'],
            'delete_staff' => ['delete_all_staff', 'staff', 'Delete all'],
        ];

        DB::transaction(function () use ($aliases): void {
            foreach ($aliases as $legacy => [$slug, $module, $name]) {
                foreach (DB::table('permissions')->where('slug', $legacy)->get() as $permission) {
                    $target = DB::table('permissions')->where('company_id', $permission->company_id)->where('slug', $slug)->first();
                    $targetId = $target?->id ?? DB::table('permissions')->insertGetId([
                        'company_id' => $permission->company_id, 'slug' => $slug,
                        'name' => $name, 'module' => $module, 'status' => $permission->status,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                    if (! $permission->status) {
                        continue;
                    }
                    $roles = DB::table('role_permissions')->join('roles', 'roles.id', '=', 'role_permissions.role_id')
                        ->where('permission_id', $permission->id)->where('roles.company_id', $permission->company_id)->pluck('roles.id');
                    foreach ($roles as $roleId) {
                        DB::table('role_permissions')->insertOrIgnore([
                            'role_id' => $roleId, 'permission_id' => $targetId,
                            'created_at' => now(), 'updated_at' => now(),
                        ]);
                    }
                }
            }
        });
    }

    /** Legacy records remain for audit; rolling back must not revoke subsequently edited role access. */
    public function down(): void {}
};
