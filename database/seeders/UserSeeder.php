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

        // Test kullanıcıları - Admin rolü ver
        $huseyin = User::firstOrCreate(
            ['email' => 'huseyinusluu@gmail.com'],
            [
                'name' => 'Hüseyin USLU',
                'password' => Hash::make('h123465'),
                'email_verified_at' => now(),
            ]
        );

        // Hüseyin'e admin rolü ver
        $huseyin->assignRole('admin');
    }
}
