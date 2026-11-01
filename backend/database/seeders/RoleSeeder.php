<?php

namespace Database\Seeders;

use App\Domain\Tenancy\Services\Roles;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/** Global roles (tenant_id null) shared by all tenants; assignment is per tenant (spatie teams). */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach (Roles::PERMISSIONS as $name) {
            Permission::findOrCreate($name, 'web');
        }
        foreach (Roles::map() as $role => $permissions) {
            $r = Role::query()->firstOrCreate(['name' => $role, 'guard_name' => 'web', 'tenant_id' => null]);
            $r->syncPermissions($permissions);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
