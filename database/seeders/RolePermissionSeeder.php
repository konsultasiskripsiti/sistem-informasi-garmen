<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'dashboard.view',
            'suppliers.view',
            'raw-materials.view',
            'products.view',
            'purchases.view',
            'productions.view',
            'sales.view',
            'stock-opname.view',
            'reports.view',
            'users.view',
            'roles.view',
            'permissions.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $owner = Role::findOrCreate('owner', 'web');
        $admin = Role::findOrCreate('admin', 'web');
        $staff = Role::findOrCreate('staff', 'web');

        $owner->syncPermissions(Permission::all());
        $admin->syncPermissions(Permission::all());
        $staff->syncPermissions([
            'purchases.view',
            'productions.view',
            'sales.view',
            'stock-opname.view',
            'reports.view',
        ]);

        $firstUser = User::query()->orderBy('id')->first();

        if ($firstUser && ! $firstUser->hasRole('admin')) {
            $firstUser->assignRole('admin');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
