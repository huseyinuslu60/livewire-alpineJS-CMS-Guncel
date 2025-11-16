<?php

namespace Database\Seeders;

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
        // Settings permissions
        $permissions = [
            'view settings',
            'edit settings',
            'manage menu',
        ];

        // Permission'lar zaten RolePermissionSeeder'da oluşturuluyor
        // Sadece role'lere atama yapıyoruz

        // Süper Admin role'e settings permission'larını ver
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($permissions);
        }

        // Admin role'e settings permission'larını ver
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }

        // Admin kullanıcısına da direkt permission ver
        $adminUser = \App\Models\User::whereHas('roles', function ($q) {
            $q->where('name', 'admin');
        })->first();

        if ($adminUser) {
            $adminUser->givePermissionTo($permissions);
        }

        // Editor role'e sadece view permission ver
        $editorRole = Role::where('name', 'editor')->first();
        if ($editorRole) {
            $editorRole->givePermissionTo(['view settings']);
        }
    }
}
