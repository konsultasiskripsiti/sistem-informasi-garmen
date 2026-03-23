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
            'users.view',
            'roles.view',
            'permissions.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $superAdmin = Role::findOrCreate('super-admin', 'web');
        $admin = Role::findOrCreate('admin', 'web');
        $staff = Role::findOrCreate('staff', 'web');

        $superAdmin->syncPermissions(Permission::all());
        $admin->syncPermissions([
            'dashboard.view',
            'suppliers.view',
            'raw-materials.view',
            'products.view',
            'purchases.view',
            'productions.view',
            'sales.view',
            'users.view',
            'roles.view',
            'permissions.view',
        ]);
        $staff->syncPermissions([
            'dashboard.view',
            'suppliers.view',
            'raw-materials.view',
            'products.view',
            'purchases.view',
            'productions.view',
            'sales.view',
            'users.view',
        ]);

        $firstUser = User::query()->orderBy('id')->first();

        if ($firstUser && ! $firstUser->hasRole('super-admin')) {
            $firstUser->assignRole('super-admin');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
