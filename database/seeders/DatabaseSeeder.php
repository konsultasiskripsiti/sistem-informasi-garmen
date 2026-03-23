<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::firstOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
            'password' => 'password',
        ]);

        $this->call(RolePermissionSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(SupplierSeeder::class);
        $this->call(RawMaterialSeeder::class);
        $this->call(ProductSeeder::class);
        $this->call(ProductionSeeder::class);
        $this->call(SaleSeeder::class);
        $this->call(NotificationSeeder::class);

        if (! $user->hasRole('super-admin')) {
            $user->assignRole('super-admin');
        }
    }
}
