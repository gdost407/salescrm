<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Company::query()->select('id')->chunkById(100, function ($companies): void {
            foreach ($companies as $company) {
                $this->seedCompany($company->id);
            }
        });
    }

    public function seedCompany(int $companyId): void
    {
        DB::transaction(function () use ($companyId): void {
            foreach (Permission::MODULES as $module => $permissions) {
                foreach ($permissions as $slug => $name) {
                    Permission::query()->firstOrCreate(
                        ['company_id' => $companyId, 'slug' => $slug],
                        ['module' => $module, 'name' => $name, 'status' => true]
                    );
                }
            }

            $aliases = [
                'view_leads' => 'view_own_leads',
                'view_own_staff' => 'view_staff', 'view_all_staff' => 'view_staff',
                'edit_own_staff' => 'edit_staff', 'edit_all_staff' => 'edit_staff',
                'delete_own_staff' => 'delete_staff', 'delete_all_staff' => 'delete_staff',
            ];
            foreach ($aliases as $oldSlug => $newSlug) {
                $legacy = Permission::query()->where('company_id', $companyId)->where('slug', $oldSlug)->first();
                if (! $legacy) {
                    continue;
                }
                $target = Permission::query()->where('company_id', $companyId)->where('slug', $newSlug)->firstOrFail();
                foreach ($legacy->roles()->where('roles.company_id', $companyId)->get() as $role) {
                    if ($legacy->status) {
                        $role->permissions()->syncWithoutDetaching([$target->id]);
                    }
                    $role->permissions()->detach($legacy->id);
                }
            }

            $admin = Role::query()->firstOrCreate(['company_id' => $companyId, 'slug' => 'admin'], [
                'name' => 'Administrator', 'description' => 'Company module access', 'status' => true,
            ]);
            if ($admin->wasRecentlyCreated) {
                $admin->permissions()->sync(Permission::query()->where('company_id', $companyId)
                    ->whereIn('slug', array_keys(array_merge(...array_values(Permission::MODULES))))->pluck('id'));
            }
            $staff = Role::query()->firstOrCreate(['company_id' => $companyId, 'slug' => 'sales-staff'], [
                'name' => 'Sales Staff', 'description' => 'Assigned leads', 'status' => true,
            ]);
            if ($staff->wasRecentlyCreated) {
                $staff->permissions()->sync(Permission::query()->where('company_id', $companyId)
                    ->whereIn('slug', ['create_leads', 'view_own_leads', 'edit_own_leads'])->pluck('id'));
            }
        });
    }
}
