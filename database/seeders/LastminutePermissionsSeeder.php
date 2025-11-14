<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class LastminutePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lastminutes permissions
        $permissions = [
            'view lastminutes',
            'create lastminutes',
            'edit lastminutes',
            'delete lastminutes',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ], [
                'module' => 'lastminutes',
            ]);
        }

        // Süper Admin role permissions
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo([
                'view lastminutes',
                'create lastminutes',
                'edit lastminutes',
                'delete lastminutes',
            ]);
        }

        // Admin role permissions
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo([
                'view lastminutes',
                'create lastminutes',
                'edit lastminutes',
                'delete lastminutes',
            ]);
        }

        // Editor role permissions
        $editorRole = Role::where('name', 'editor')->first();
        if ($editorRole) {
            $editorRole->givePermissionTo([
                'view lastminutes',
                'create lastminutes',
                'edit lastminutes',
            ]);
        }
    }
}
