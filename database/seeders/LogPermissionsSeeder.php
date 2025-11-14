<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class LogPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $logPermissions = [
            'view logs' => 'Logları Görüntüleme',
            'delete logs' => 'Logları Silme',
            'export logs' => 'Logları Dışa Aktarma',
        ];

        foreach ($logPermissions as $name => $description) {
            Permission::updateOrCreate(
                ['name' => $name],
                [
                    'guard_name' => 'web',
                    'description' => $description,
                    'module' => 'logs',
                ]
            );
        }

        $this->command->info('Log izinleri Türkçe olarak güncellendi!');
    }
}
