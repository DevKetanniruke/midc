<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Permissions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates the permissions, the built-in roles and one sample user per role (password: "password").
 * Safe to re-run: the built-in roles only get their default permissions the first time, so edits made
 * on the Roles page are kept. The admin role always holds every permission.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Permissions::all() as $name) {
            Permission::findOrCreate($name, 'web');
        }

        foreach (array_keys(Permissions::SYSTEM_ROLES) as $name) {
            $role = Role::findOrCreate($name, 'web');

            if ($name === 'admin') {
                $role->syncPermissions(Permissions::all());
            } elseif ($role->permissions()->doesntExist()) {
                $role->syncPermissions(Permissions::DEFAULTS[$name] ?? []);
            }
        }

        User::where('email', 'admin@example.com')->first()?->assignRole('admin');

        $samples = [
            ['hod.production@example.com', 'Production Head', 'Production', 'dept_head'],
            ['hod.maintenance@example.com', 'Maintenance Head', 'Maintenance', 'dept_head'],
            ['hr@example.com', 'HR Manager', 'HR', 'hr'],
            ['security@example.com', 'Security Officer', 'Security', 'security'],
        ];

        foreach ($samples as [$email, $name, $department, $role]) {
            // Only create missing samples; never touch a user that was edited in the app.
            $user = User::firstOrCreate(['email' => $email], [
                'name' => $name,
                'department' => $department,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);

            if ($user->wasRecentlyCreated) {
                $user->assignRole($role);
            }
        }
    }
}
