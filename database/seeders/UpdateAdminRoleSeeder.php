<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UpdateAdminRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Süper Admin role'ünü bul
        $superAdminRole = Role::where('name', 'super_admin')->first();

        if ($superAdminRole) {
            // Tüm permission'ları süper admin role'üne ata
            $allPermissions = Permission::all();
            $superAdminRole->syncPermissions($allPermissions);
            $this->command->info('Süper Admin role updated with all permissions: '.$allPermissions->count());
        }

        // Admin role'ünü bul
        $adminRole = Role::where('name', 'admin')->first();

        if (! $adminRole) {
            $this->command->error('Admin role not found!');

            return;
        }

        // Modül yönetimi permission'larını hariç tut (sadece super_admin'e verilecek)
        $modulePermissions = ['view modules', 'edit modules', 'activate modules'];
        $allPermissions = Permission::all()->reject(function ($permission) use ($modulePermissions) {
            return in_array($permission->name, $modulePermissions);
        });

        // Admin role'üne modül yönetimi hariç tüm permission'ları ata
        $adminRole->syncPermissions($allPermissions);

        $this->command->info('Admin role updated with all permissions (except module management): '.$allPermissions->count());
    }
}
