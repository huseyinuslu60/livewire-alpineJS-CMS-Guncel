<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class BanksPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Banks modülü yetkileri
        $permissions = [
            'view stocks',
            'create stocks',
            'edit stocks',
            'delete stocks',
            'view investor_questions',
            'edit investor_questions',
            'delete investor_questions',
        ];

        // Yetkileri oluştur
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ], [
                'module' => 'banks',
            ]);
        }

        // Süper Admin rolüne tüm yetkileri ver
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($permissions);
        }

        // Admin rolüne tüm yetkileri ver
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }

        // Editor rolüne sadece görüntüleme ve düzenleme yetkileri ver
        $editorRole = Role::where('name', 'editor')->first();
        if ($editorRole) {
            $editorPermissions = [
                'view stocks',
                'create stocks',
                'edit stocks',
                'view investor_questions',
                'edit investor_questions',
            ];
            $editorRole->givePermissionTo($editorPermissions);
        }

        $this->command->info('Banks modülü yetkileri başarıyla eklendi!');
    }
}
