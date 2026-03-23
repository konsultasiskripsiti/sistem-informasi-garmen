<?php

namespace Database\Seeders;

use App\Models\User;
use App\Notifications\AdminPanelNotification;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::orderBy('id')->take(5)->get();

        foreach ($users as $user) {
            $user->notifications()->delete();

            $user->notify(new AdminPanelNotification(
                title: 'Welcome to the admin panel',
                message: 'Your account is ready to manage users, roles, and permissions.',
                routeName: 'dashboard',
                type: 'System'
            ));

            $user->notify(new AdminPanelNotification(
                title: 'Review user access',
                message: 'Please review role assignments for the latest registered users.',
                routeName: 'users.index',
                type: 'Users'
            ));

            $user->notify(new AdminPanelNotification(
                title: 'Permissions updated',
                message: 'Several permissions were prepared for the access control module.',
                routeName: 'permissions.index',
                type: 'Permissions'
            ));

            $user->notify(new AdminPanelNotification(
                title: 'Roles need confirmation',
                message: 'Check the role matrix to make sure each role has the correct access.',
                routeName: 'roles.index',
                type: 'Roles'
            ));
        }
    }
}
