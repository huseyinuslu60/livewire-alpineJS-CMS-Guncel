<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AgencyNewsPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Agency News permissions
        $permissions = [
            'view agency_news',
            'delete agency_news',
            'publish agency_news',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ], [
                'module' => 'agencynews',
            ]);
        }

        // Süper Admin role permissions
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo([
                'view agency_news',
                'delete agency_news',
                'publish agency_news',
            ]);
        }

        // Admin role permissions
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo([
                'view agency_news',
                'delete agency_news',
                'publish agency_news',
            ]);
        }

        // Editor role permissions
        $editorRole = Role::where('name', 'editor')->first();
        if ($editorRole) {
            $editorRole->givePermissionTo([
                'view agency_news',
                'publish agency_news',
            ]);
        }
    }
}
