<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SuperAdminPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Süper Admin kullanıcısını bul
        $superAdmin = User::where('email', 'huseyinusluu@gmail.com')->first();

        if ($superAdmin) {
            // Süper Admin rolünü bul veya oluştur
            $superAdminRole = Role::where('name', 'super_admin')->first();

            if ($superAdminRole) {
                // Kullanıcıya süper admin rolünü ata
                $superAdmin->assignRole('super_admin');

                // Tüm permission'ları süper admin'e ver
                $superAdmin->syncPermissions(Permission::all());

                $this->command->info('Süper Admin permissions synced successfully!');
                $this->command->info('Total permissions: '.Permission::count());
            } else {
                $this->command->error('Super Admin role not found!');
            }
        } else {
            $this->command->error('Super Admin user not found!');
        }
    }
}
