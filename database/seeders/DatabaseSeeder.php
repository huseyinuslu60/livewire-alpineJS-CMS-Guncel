<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Temel seeder'lar
            RolePermissionSeeder::class,
            UserSeeder::class,

            // Modül seeder'ları
            ModuleSeeder::class,
            MenuItemsSeeder::class,

            // Permission seeder'ları
            SuperAdminPermissionsSeeder::class,
            AgencyNewsPermissionsSeeder::class,
            FilesPermissionsSeeder::class,
            LastminutePermissionsSeeder::class,
            LogPermissionsSeeder::class,
            SettingsPermissionsSeeder::class,
            BanksPermissionSeeder::class,
            NewsletterPermissionsSeeder::class,

            // Newsletter template seeder'ı
            NewsletterTemplateSeeder::class,

            // Test seeder'ları
            AgencyNewsTestSeeder::class,

            // Update seeder'ları
            UpdateAdminRoleSeeder::class,

            // Site ayarları
            SiteSettingsSeeder::class,
        ]);
    }
}
