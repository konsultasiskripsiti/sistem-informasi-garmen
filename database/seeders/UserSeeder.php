<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Ari Saputra', 'email' => 'ari.saputra@example.com', 'role' => 'super-admin'],
            ['name' => 'Budi Santoso', 'email' => 'budi.santoso@example.com', 'role' => 'admin'],
            ['name' => 'Citra Lestari', 'email' => 'citra.lestari@example.com', 'role' => 'admin'],
            ['name' => 'Dewi Anggraini', 'email' => 'dewi.anggraini@example.com', 'role' => 'admin'],
            ['name' => 'Eko Prasetyo', 'email' => 'eko.prasetyo@example.com', 'role' => 'admin'],
            ['name' => 'Fajar Nugroho', 'email' => 'fajar.nugroho@example.com', 'role' => 'staff'],
            ['name' => 'Gita Permata', 'email' => 'gita.permata@example.com', 'role' => 'staff'],
            ['name' => 'Hendra Wijaya', 'email' => 'hendra.wijaya@example.com', 'role' => 'staff'],
            ['name' => 'Indah Sari', 'email' => 'indah.sari@example.com', 'role' => 'staff'],
            ['name' => 'Joko Setiawan', 'email' => 'joko.setiawan@example.com', 'role' => 'staff'],
            ['name' => 'Kiki Amelia', 'email' => 'kiki.amelia@example.com', 'role' => 'staff'],
            ['name' => 'Lukman Hakim', 'email' => 'lukman.hakim@example.com', 'role' => 'staff'],
            ['name' => 'Maya Puspita', 'email' => 'maya.puspita@example.com', 'role' => 'staff'],
            ['name' => 'Nanda Putri', 'email' => 'nanda.putri@example.com', 'role' => 'staff'],
            ['name' => 'Oki Ramadhan', 'email' => 'oki.ramadhan@example.com', 'role' => 'staff'],
            ['name' => 'Putri Maharani', 'email' => 'putri.maharani@example.com', 'role' => 'staff'],
            ['name' => 'Qori Aulia', 'email' => 'qori.aulia@example.com', 'role' => 'staff'],
            ['name' => 'Rizky Maulana', 'email' => 'rizky.maulana@example.com', 'role' => 'staff'],
            ['name' => 'Sinta Melati', 'email' => 'sinta.melati@example.com', 'role' => 'staff'],
            ['name' => 'Teguh Firmansyah', 'email' => 'teguh.firmansyah@example.com', 'role' => 'staff'],
        ];

        foreach ($users as $item) {
            $user = User::updateOrCreate(
                ['email' => $item['email']],
                [
                    'name' => $item['name'],
                    'password' => 'password',
                    'email_verified_at' => now(),
                ]
            );

            if (! $user->hasRole($item['role'])) {
                $user->syncRoles([$item['role']]);
            }
        }
    }
}
