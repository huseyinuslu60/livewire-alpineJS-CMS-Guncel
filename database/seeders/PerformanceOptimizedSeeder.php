<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PerformanceOptimizedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. ROLES - Laravel Permission package structure
        $roles = [
            [
                'name' => 'admin',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'editor',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'author',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'subscriber',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('roles')->insert($roles);

        // 2. PERMISSIONS - Tüm permission'lar
        $permissions = [
            // User Management
            ['name' => 'view users', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'create users', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'edit users', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'delete users', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],

            // Article Management
            ['name' => 'view articles', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'create articles', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'edit articles', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'delete articles', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'publish articles', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],

            // Posts Management
            ['name' => 'view posts', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'create posts', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'edit posts', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'delete posts', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'publish posts', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],

            // Author Management
            ['name' => 'view authors', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'create authors', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'edit authors', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'delete authors', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],

            // Role Management
            ['name' => 'view roles', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'create roles', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'edit roles', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'delete roles', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],

            // Category Management
            ['name' => 'view categories', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'create categories', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'edit categories', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'delete categories', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],

            // Comments Management
            ['name' => 'view comments', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'delete comments', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'approve comments', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'reject comments', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],

            // Logs Management
            ['name' => 'view logs', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'delete logs', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],

            // Featured Management
            ['name' => 'view featured', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'create featured', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'edit featured', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'delete featured', 'module' => 'web', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('permissions')->insert($permissions);

        // 3. ROLE_HAS_PERMISSIONS - Admin tüm yetkilere sahip
        $adminRoleId = DB::table('roles')->where('name', 'admin')->first()->id;
        $permissionIds = DB::table('permissions')->pluck('id');

        $rolePermissions = [];
        foreach ($permissionIds as $permissionId) {
            $rolePermissions[] = [
                'permission_id' => $permissionId,
                'role_id' => $adminRoleId,
            ];
        }
        DB::table('role_has_permissions')->insert($rolePermissions);

        // 4. USERS
        $users = [
            [
                'name' => 'Hüseyin Uslu',
                'email' => 'huseyinusluu@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'avatar' => null,
                'phone' => null,
                'is_active' => true,
                'last_login_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Editor User',
                'email' => 'editor@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'avatar' => null,
                'phone' => null,
                'is_active' => true,
                'last_login_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Author User',
                'email' => 'author@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'avatar' => null,
                'phone' => null,
                'is_active' => true,
                'last_login_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('users')->insert($users);

        // 5. MODEL_HAS_ROLES - Laravel Permission package structure
        $adminUserId = DB::table('users')->where('email', 'huseyinusluu@gmail.com')->first()->id;
        $editorUserId = DB::table('users')->where('email', 'editor@example.com')->first()->id;
        $authorUserId = DB::table('users')->where('email', 'author@example.com')->first()->id;

        $adminRoleId = DB::table('roles')->where('name', 'admin')->first()->id;
        $editorRoleId = DB::table('roles')->where('name', 'editor')->first()->id;
        $authorRoleId = DB::table('roles')->where('name', 'author')->first()->id;

        $userRoles = [
            ['role_id' => $adminRoleId, 'model_type' => 'App\\Models\\User', 'model_id' => $adminUserId],
            ['role_id' => $editorRoleId, 'model_type' => 'App\\Models\\User', 'model_id' => $editorUserId],
            ['role_id' => $authorRoleId, 'model_type' => 'App\\Models\\User', 'model_id' => $authorUserId],
        ];

        DB::table('model_has_roles')->insert($userRoles);

        // 6. CATEGORIES
        $categories = [
            [
                'name' => 'Haber',
                'slug' => 'haber',
                'description' => 'Genel haber kategorisi',
                'color' => '#3B82F6',
                'parent_id' => null,
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Spor',
                'slug' => 'spor',
                'description' => 'Spor haberleri',
                'color' => '#10B981',
                'parent_id' => null,
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ekonomi',
                'slug' => 'ekonomi',
                'description' => 'Ekonomi haberleri',
                'color' => '#F59E0B',
                'parent_id' => null,
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Teknoloji',
                'slug' => 'teknoloji',
                'description' => 'Teknoloji haberleri',
                'color' => '#8B5CF6',
                'parent_id' => null,
                'sort_order' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('categories')->insert($categories);

        // 7. TAGS
        $tags = [
            ['name' => 'Gündem', 'slug' => 'gundem', 'color' => '#EF4444', 'usage_count' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Son Dakika', 'slug' => 'son-dakika', 'color' => '#F97316', 'usage_count' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Önemli', 'slug' => 'onemli', 'color' => '#84CC16', 'usage_count' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Analiz', 'slug' => 'analiz', 'color' => '#06B6D4', 'usage_count' => 0, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('tags')->insert($tags);

        // 8. MENU_ITEMS
        $menuItems = [
            // Ana Menüler
            [
                'name' => 'dashboard',
                'title' => 'Dashboard',
                'url' => '/admin/dashboard',
                'route' => 'admin.dashboard',
                'icon' => 'fas fa-tachometer-alt',
                'type' => 'link',
                'parent_id' => null,
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'content_management',
                'title' => 'İçerik Yönetimi',
                'url' => null,
                'route' => null,
                'icon' => 'fas fa-newspaper',
                'type' => 'module',
                'parent_id' => null,
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'user_management',
                'title' => 'Kullanıcı Yönetimi',
                'url' => null,
                'route' => null,
                'icon' => 'fas fa-users',
                'type' => 'module',
                'parent_id' => null,
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'system_settings',
                'title' => 'Sistem Ayarları',
                'url' => null,
                'route' => null,
                'icon' => 'fas fa-cog',
                'type' => 'module',
                'parent_id' => null,
                'sort_order' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('menu_items')->insert($menuItems);

        // Alt menüleri ekle
        $contentManagementId = DB::table('menu_items')->where('name', 'content_management')->first()->id;
        $userManagementId = DB::table('menu_items')->where('name', 'user_management')->first()->id;
        $systemSettingsId = DB::table('menu_items')->where('name', 'system_settings')->first()->id;

        $subMenuItems = [
            // İçerik Yönetimi Alt Menüleri
            [
                'name' => 'posts_list',
                'title' => 'İçerikler',
                'url' => '/admin/posts',
                'route' => 'admin.posts.index',
                'icon' => 'fas fa-list',
                'type' => 'link',
                'parent_id' => $contentManagementId,
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'posts_create',
                'title' => 'Yeni İçerik',
                'url' => '/admin/posts/create',
                'route' => 'admin.posts.create',
                'icon' => 'fas fa-plus',
                'type' => 'link',
                'parent_id' => $contentManagementId,
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'categories_management',
                'title' => 'Kategori Yönetimi',
                'url' => '/admin/categories',
                'route' => 'admin.categories.index',
                'icon' => 'fas fa-tags',
                'type' => 'link',
                'parent_id' => $contentManagementId,
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Kullanıcı Yönetimi Alt Menüleri
            [
                'name' => 'users_list',
                'title' => 'Kullanıcılar',
                'url' => '/admin/users',
                'route' => 'admin.users.index',
                'icon' => 'fas fa-users',
                'type' => 'link',
                'parent_id' => $userManagementId,
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'roles_management',
                'title' => 'Rol Yönetimi',
                'url' => '/admin/roles',
                'route' => 'admin.roles.index',
                'icon' => 'fas fa-user-shield',
                'type' => 'link',
                'parent_id' => $userManagementId,
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Sistem Ayarları Alt Menüleri
            [
                'name' => 'menu_management',
                'title' => 'Menü Yönetimi',
                'url' => '/admin/menu',
                'route' => 'admin.menu.index',
                'icon' => 'fas fa-bars',
                'type' => 'link',
                'parent_id' => $systemSettingsId,
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'site_settings',
                'title' => 'Site Ayarları',
                'url' => '/admin/settings',
                'route' => 'admin.settings.index',
                'icon' => 'fas fa-cog',
                'type' => 'link',
                'parent_id' => $systemSettingsId,
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('menu_items')->insert($subMenuItems);

        // 9. SITE_SETTINGS
        $siteSettings = [
            ['key' => 'site_name', 'value' => 'Borsa Haber', 'type' => 'string', 'description' => 'Site adı'],
            ['key' => 'site_description', 'value' => 'Güncel borsa haberleri ve analizleri', 'type' => 'text', 'description' => 'Site açıklaması'],
            ['key' => 'site_logo', 'value' => '/images/logo.png', 'type' => 'string', 'description' => 'Site logosu'],
            ['key' => 'contact_email', 'value' => 'info@borsahaber.com', 'type' => 'string', 'description' => 'İletişim e-postası'],
            ['key' => 'contact_phone', 'value' => '+90 212 555 0123', 'type' => 'string', 'description' => 'İletişim telefonu'],
            ['key' => 'social_facebook', 'value' => 'https://facebook.com/borsahaber', 'type' => 'string', 'description' => 'Facebook sayfası'],
            ['key' => 'social_twitter', 'value' => 'https://twitter.com/borsahaber', 'type' => 'string', 'description' => 'Twitter hesabı'],
            ['key' => 'social_instagram', 'value' => 'https://instagram.com/borsahaber', 'type' => 'string', 'description' => 'Instagram hesabı'],
        ];

        DB::table('site_settings')->insert($siteSettings);

        $this->command->info('Performance optimized seeder completed successfully!');
    }
}
