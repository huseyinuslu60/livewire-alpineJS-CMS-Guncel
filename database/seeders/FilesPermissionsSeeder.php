<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FilesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Files permissions
        $permissions = [
            'view files',
            'create files',
            'edit files',
            'delete files',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ], [
                'module' => 'files',
            ]);
        }

        // Süper Admin role'e files permission'larını ver
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($permissions);
        }

        // Admin role'e files permission'larını ver
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }

        // Editor role'e files permission'larını ver
        $editorRole = Role::where('name', 'editor')->first();
        if ($editorRole) {
            $editorRole->givePermissionTo($permissions);
        }

        // Yazar rolüne files permission'ları VERİLMEYECEK (sadece makale yetkileri var)

        $this->command->info('Files permissions created and assigned to roles.');
    }
}
