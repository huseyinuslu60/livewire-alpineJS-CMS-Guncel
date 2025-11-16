<?php

namespace Database\Seeders;

// use App\Models\AuthorProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User Management
            'view users',
            'create users',
            'edit users',
            'delete users',

            // Article Management
            'view articles',
            'view all articles',
            'create articles',
            'edit articles',
            'delete own articles',
            'delete all articles',
            'delete articles',
            'publish articles',

            // Posts Management
            'view posts',
            'create posts',
            'edit posts',
            'delete posts',
            'publish posts',

            // Author Management
            'view authors',
            'create authors',
            'edit authors',
            'delete authors',

            // Role Management
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',

            // Category Management
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',

            // Comments Management
            'view comments',
            'delete comments',
            'approve comments',
            'reject comments',
            'update comments',

            // Logs Management
            'view logs',
            'delete logs',
            'export logs',

            // Featured Management (Vitrin Yönetimi)
            'view featured',
            'create featured',
            'edit featured',
            'delete featured',

            // Agency News Management
            'view agency_news',
            'delete agency_news',
            'publish agency_news',

            // Banks modülü yetkileri
            'view stocks',
            'create stocks',
            'edit stocks',
            'delete stocks',
            'view investor_questions',
            'edit investor_questions',
            'delete investor_questions',

            // Settings Management
            'view settings',
            'edit settings',
            'manage menu',

            // Last Minute Management
            'view lastminutes',
            'create lastminutes',
            'edit lastminutes',
            'delete lastminutes',

            // Files Management
            'view files',
            'create files',
            'edit files',
            'delete files',

            // Newsletter Management
            'view newsletters',
            'create newsletters',
            'edit newsletters',
            'delete newsletters',
            'view newsletter_users',
            'edit newsletter_users',
            'delete newsletter_users',
            'view newsletter_logs',
            'delete newsletter_logs',

            // Modül Yönetimi
            'view modules',
            'edit modules',
            'activate modules',

        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ], [
                'module' => 'web',
            ]);
        }

        // Create roles and assign permissions
        // Süper Admin rolü - Sistemdeki tüm yetkilere sahip en üst seviye rol
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'super_admin'],
            [
                'display_name' => 'Süper Admin',
                'description' => 'Sistemdeki tüm yetkilere sahip en üst seviye kullanıcı.',
            ]
        );

        // Süper Admin'e tüm permission'ları ata
        $superAdminRole->syncPermissions(Permission::all());

        $this->command->info('Süper Admin role created with all permissions: '.Permission::count());

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin', 'description' => 'Sistem üzerinde tam yetkiye sahip kullanıcı.']);

        // Modül yönetimi permission'larını hariç tut (sadece super_admin'e verilecek)
        $modulePermissions = ['view modules', 'edit modules', 'activate modules'];
        $adminPermissions = Permission::all()->reject(function ($permission) use ($modulePermissions) {
            return in_array($permission->name, $modulePermissions);
        });

        // Admin'e modül yönetimi hariç tüm permission'ları ata
        $adminRole->syncPermissions($adminPermissions);

        $this->command->info('Admin role updated with all permissions (except module management): '.$adminPermissions->count());

        $yazarRole = Role::firstOrCreate(['name' => 'yazar'], ['display_name' => 'Yazar', 'description' => 'Kendi makalelerini oluşturabilen ve düzenleyebilen kullanıcı.']);
        $yazarRole->givePermissionTo([
            'view articles',        // Sadece kendi makalelerini görür
            'create articles',
            'edit articles',        // Sadece kendi makalelerini düzenler
            'delete own articles',  // Sadece kendi makalelerini siler
            'publish articles',     // Sadece kendi makalelerini yayınlar
        ]);

        $editorRole = Role::firstOrCreate(['name' => 'editor'], ['display_name' => 'Editör', 'description' => 'Makaleleri yayınlayabilen ve düzenleyebilen kullanıcı.']);
        $editorRole->givePermissionTo([
            'view all articles',    // Tüm makaleleri görür
            'create articles',
            'edit articles',        // Tüm makaleleri düzenler
            'delete all articles',  // Tüm makaleleri siler
            'publish articles',     // Tüm makaleleri yayınlar
            'view categories',
            'create categories',
            'edit categories',
            'view featured',
            'create featured',
            'edit featured',
            'delete featured',
            'view posts',
            'create posts',
            'edit posts',
            'publish posts',
            'view files',
            'create files',
            'edit files',
            'delete files',
            'view comments',
            'approve comments',
            'reject comments',
            'update comments',
            'view lastminutes',
            'create lastminutes',
            'edit lastminutes',
            'delete lastminutes',
            'view newsletters',
            'create newsletters',
            'edit newsletters',
            'view newsletter_users',
            'view newsletter_logs',
        ]);

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
            ]
        );
        $admin->assignRole('admin');

        // Create yazar user
        $yazar = User::firstOrCreate(
            ['email' => 'yazar@example.com'],
            [
                'name' => 'Hüseyin USLU',
                'password' => bcrypt('password'),
            ]
        );
        $yazar->assignRole('yazar');

        // Create editor user
        $editor = User::firstOrCreate(
            ['email' => 'editor@example.com'],
            [
                'name' => 'Editör Kullanıcı',
                'password' => bcrypt('password'),
            ]
        );
        $editor->assignRole('editor');

        // Create author profile for yazar (if AuthorProfile model exists)
        // AuthorProfile::firstOrCreate(
        //     ['user_id' => $yazar->id],
        //     [
        //         'title' => 'Teknoloji Yazarı',
        //         'bio' => 'Teknoloji dünyasından haberler ve analizler.',
        //         'twitter' => '@huseyinuslu',
        //         'status' => true,
        //     ]
        // );
    }
}
