<?php

namespace Modules\Settings\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SettingsPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Permission'lar zaten RolePermissionSeeder'da oluşturuluyor
        // Sadece role'lere atama yapıyoruz

        $permissions = [
            'view settings',
            'edit settings',
            'manage menu',
        ];

        // Admin role permissions (zaten RolePermissionSeeder'da atanmış olabilir)
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            foreach ($permissions as $permission) {
                try {
                    $adminRole->givePermissionTo($permission);
                } catch (\Exception $e) {
                    // Permission zaten atanmış olabilir, devam et
                }
            }
        }

        // Editor role permissions
        $editorRole = Role::where('name', 'editor')->first();
        if ($editorRole) {
            try {
                $editorRole->givePermissionTo('view settings');
            } catch (\Exception $e) {
                // Permission zaten atanmış olabilir, devam et
            }
        }
    }
}
