<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Menyederhanakan role menjadi owner, admin, dan staff.
 *
 * - User yang sebelumnya ber-role super-admin dipindahkan ke admin.
 * - Role super-admin dihapus.
 * - Permission baru stock-opname.view dan reports.view dibuat.
 * - Owner dan admin mendapat semua permission.
 * - Staff hanya mendapat akses transaksi, stok opname, dan laporan.
 */
return new class extends Migration
{
    public function up(): void
    {
        $registrar = app(PermissionRegistrar::class);
        $registrar->forgetCachedPermissions();

        foreach (['dashboard.view', 'stock-opname.view', 'reports.view'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $owner = Role::findOrCreate('owner', 'web');
        $admin = Role::findOrCreate('admin', 'web');
        $staff = Role::findOrCreate('staff', 'web');

        $superAdmin = Role::query()
            ->where('name', 'super-admin')
            ->where('guard_name', 'web')
            ->first();

        if ($superAdmin) {
            foreach ($superAdmin->users()->get() as $user) {
                $user->assignRole($admin);
            }

            $superAdmin->delete();
        }

        foreach (['purchases.view', 'productions.view', 'sales.view'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $allPermissions = Permission::query()->where('guard_name', 'web')->get();

        $owner->syncPermissions($allPermissions);
        $admin->syncPermissions($allPermissions);
        $staff->syncPermissions([
            'purchases.view',
            'productions.view',
            'sales.view',
            'stock-opname.view',
            'reports.view',
        ]);

        $registrar->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Migrasi data ini tidak dibalik: tidak ada catatan user mana yang
        // dulunya super-admin, sehingga role tersebut tidak bisa dipulihkan.
    }
};
