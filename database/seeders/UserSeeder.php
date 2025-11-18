<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin kullanıcısı zaten RolePermissionSeeder'da oluşturuluyor
        // Bu seeder sadece ek kullanıcılar için kullanılabilir

        // Hüseyin USLU - Sadece Super Admin rolü
        $huseyin = User::firstOrCreate(
            ['email' => 'huseyinusluu@gmail.com'],
            [
                'name' => 'Hüseyin USLU',
                'password' => Hash::make('h123465'),
                'email_verified_at' => now(),
            ]
        );

        // Tüm rolleri kaldır ve sadece super admin rolü ver
        $huseyin->syncRoles(['super_admin']);
    }
}
