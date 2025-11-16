<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class NewsletterPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Newsletter permissions - sadece temel permissions
        $permissions = [
            'view newsletters',
            'create newsletters',
            'edit newsletters',
            'delete newsletters',
            'view newsletter_users',
            'edit newsletter_users',
            'delete newsletter_users',
            'view newsletter_logs',
            'delete newsletter_logs',
        ];

        // Create permissions safely
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ], [
                'module' => 'newsletters',
            ]);
        }

        // Assign permissions to super admin role
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            try {
                $superAdminRole->givePermissionTo($permissions);
                $this->command->info('Newsletter permissions assigned to super admin role');
            } catch (\Exception $e) {
                \Log::info('Error assigning permissions to super admin role: '.$e->getMessage());
            }
        }

        // Assign permissions to admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            try {
                $adminRole->givePermissionTo($permissions);
                $this->command->info('Newsletter permissions assigned to admin role');
            } catch (\Exception $e) {
                \Log::info('Error assigning permissions to admin role: '.$e->getMessage());
            }
        }

        // Assign permissions to admin user directly
        $adminUser = \App\Models\User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->first();

        if ($adminUser) {
            try {
                $adminUser->givePermissionTo($permissions);
                $this->command->info('Newsletter permissions assigned to admin user');
            } catch (\Exception $e) {
                \Log::info('Error assigning permissions to admin user: '.$e->getMessage());
            }
        }

        // Assign basic permissions to editor role
        $editorRole = Role::where('name', 'editor')->first();
        if ($editorRole) {
            $editorPermissions = [
                'view newsletters',
                'create newsletters',
                'edit newsletters',
                'view newsletter_users',
                'view newsletter_logs',
            ];
            try {
                $editorRole->givePermissionTo($editorPermissions);
            } catch (\Exception $e) {
                \Log::info('Error assigning permissions to editor role: '.$e->getMessage());
            }
        }
    }
}
