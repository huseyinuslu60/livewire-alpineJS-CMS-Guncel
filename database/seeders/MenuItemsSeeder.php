<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuItemsSeeder extends Seeder
{
    public function run(): void
    {
        // Ana menü item'ları
        $menuItems = [
            [
                'name' => 'user_management',
                'title' => 'Kullanıcı Yönetimi',
                'icon' => 'fas fa-users',
                'type' => 'module',
                'module' => 'user',
                'active_pattern' => 'user.*',
                'roles' => ['super_admin', 'admin'],
                'permission' => 'view users',
                'sort_order' => 1,
            ],
            [
                'name' => 'articles_management',
                'title' => 'Makale Yönetimi',
                'icon' => 'fas fa-file-alt',
                'type' => 'module',
                'module' => 'articles',
                'active_pattern' => 'articles.*',
                'roles' => ['super_admin', 'admin', 'yazar', 'editor'],
                'permission' => 'view articles',
                'sort_order' => 2,
            ],
            [
                'name' => 'posts_management',
                'title' => 'Haber Yönetimi',
                'icon' => 'fas fa-newspaper',
                'type' => 'module',
                'module' => 'posts',
                'active_pattern' => 'posts.*|headline.*|lastminutes.*|agencynews.*',
                'roles' => ['super_admin', 'admin', 'editor'],
                'permission' => 'view posts',
                'sort_order' => 3,
            ],
            [
                'name' => 'categories_management',
                'title' => 'Kategori Yönetimi',
                'icon' => 'fas fa-tags',
                'type' => 'module',
                'module' => 'categories',
                'active_pattern' => 'categories.*',
                'roles' => ['super_admin', 'admin'],
                'permission' => 'view categories',
                'sort_order' => 4,
            ],
            [
                'name' => 'files_management',
                'title' => 'Dosya Yönetimi',
                'icon' => 'fas fa-folder',
                'type' => 'module',
                'module' => 'files',
                'active_pattern' => 'files.*',
                'roles' => ['super_admin', 'admin', 'editor'],
                'permission' => 'view files',
                'sort_order' => 5,
            ],
            [
                'name' => 'authors_management',
                'title' => 'Yazar Yönetimi',
                'icon' => 'fas fa-user-edit',
                'type' => 'module',
                'module' => 'authors',
                'active_pattern' => 'authors.*',
                'roles' => ['super_admin', 'admin', 'editor'],
                'permission' => 'view authors',
                'sort_order' => 6,
            ],
            [
                'name' => 'comments_management',
                'title' => 'Yorum Yönetimi',
                'icon' => 'fas fa-comments',
                'type' => 'single',
                'route' => 'comments.index',
                'active_pattern' => 'comments.*',
                'roles' => ['super_admin', 'admin', 'editor'],
                'permission' => 'view comments',
                'sort_order' => 7,
            ],
            [
                'name' => 'roles_management',
                'title' => 'Rol Yönetimi',
                'icon' => 'fas fa-shield-alt',
                'type' => 'single',
                'route' => 'role.management',
                'active_pattern' => 'role.management',
                'roles' => ['super_admin', 'admin'],
                'permission' => 'view roles',
                'sort_order' => 8,
            ],
            [
                'name' => 'logs_management',
                'title' => 'Sistem Logları',
                'icon' => 'fas fa-clipboard-list',
                'type' => 'single',
                'route' => 'logs.index',
                'active_pattern' => 'logs.*',
                'module' => 'logs',
                'roles' => ['super_admin', 'admin'],
                'permission' => 'view logs',
                'sort_order' => 9,
            ],
            [
                'name' => 'modules_management',
                'title' => 'Modül Yönetimi',
                'icon' => 'fas fa-cogs',
                'type' => 'single',
                'route' => 'modules.index',
                'active_pattern' => 'modules.*',
                'roles' => ['super_admin'],
                'permission' => 'view modules',
                'sort_order' => 10,
            ],
            [
                'name' => 'settings_management',
                'title' => 'Sistem Ayarları',
                'icon' => 'fas fa-cog',
                'type' => 'module',
                'module' => 'settings',
                'active_pattern' => 'settings.*|admin.menu.*',
                'roles' => ['super_admin', 'admin'],
                'permission' => 'view settings',
                'sort_order' => 11,
            ],
            [
                'name' => 'banks_management',
                'title' => 'Hisse Yönetimi',
                'icon' => 'fas fa-chart-line',
                'type' => 'module',
                'module' => 'banks',
                'active_pattern' => 'banks.*',
                'roles' => ['super_admin', 'admin', 'editor'],
                'permission' => 'view stocks',
                'sort_order' => 12,
            ],
            [
                'name' => 'newsletters_management',
                'title' => 'Bülten Yönetimi',
                'icon' => 'fas fa-envelope',
                'type' => 'module',
                'module' => 'newsletters',
                'active_pattern' => 'newsletters.*',
                'roles' => ['super_admin', 'admin', 'editor'],
                'permission' => 'view newsletters',
                'sort_order' => 13,
                'is_active' => true,
            ],
        ];

        // Ana menüleri oluştur/güncelle ve ID'lerini al
        $createdMenus = [];
        foreach ($menuItems as $item) {
            $menu = MenuItem::updateOrCreate(
                ['name' => $item['name']], // unique key
                $item
            );
            $createdMenus[$item['name']] = $menu->id;
        }

        // Alt menü item'ları - doğru parent_id'lerle
        $subMenuItems = [
            // Kullanıcı Yönetimi alt menüleri
            [
                'name' => 'user_list',
                'title' => 'Kullanıcı Listesi',
                'type' => 'single',
                'route' => 'user.index',
                'active_pattern' => 'user.index',
                'roles' => ['super_admin', 'admin'],
                'permission' => 'view users',
                'parent_id' => $createdMenus['user_management'],
                'sort_order' => 1,
            ],
            [
                'name' => 'user_create',
                'title' => 'Yeni Kullanıcı',
                'type' => 'single',
                'route' => 'user.create',
                'active_pattern' => 'user.create',
                'roles' => ['super_admin', 'admin'],
                'permission' => 'create users',
                'parent_id' => $createdMenus['user_management'],
                'sort_order' => 2,
            ],
            // Makale Yönetimi alt menüleri
            [
                'name' => 'articles_list',
                'title' => 'Makale Listesi',
                'type' => 'single',
                'route' => 'articles.index',
                'active_pattern' => 'articles.index',
                'roles' => ['super_admin', 'admin', 'editor', 'yazar'],
                'permission' => 'view articles',
                'parent_id' => $createdMenus['articles_management'],
                'sort_order' => 1,
            ],
            [
                'name' => 'articles_create',
                'title' => 'Yeni Makale',
                'type' => 'single',
                'route' => 'articles.create',
                'active_pattern' => 'articles.create',
                'roles' => ['super_admin', 'admin', 'editor', 'yazar'],
                'permission' => 'create articles',
                'parent_id' => $createdMenus['articles_management'],
                'sort_order' => 2,
            ],
            // Haber Yönetimi alt menüleri
            [
                'name' => 'posts_list',
                'title' => 'Haber Listesi',
                'type' => 'single',
                'route' => 'posts.index',
                'active_pattern' => 'posts.index',
                'roles' => ['super_admin', 'admin', 'editor'],
                'parent_id' => $createdMenus['posts_management'],
                'sort_order' => 1,
            ],
            [
                'name' => 'posts_create_menu',
                'title' => 'Yeni İçerik',
                'type' => 'submenu',
                'active_pattern' => 'posts.create.*',
                'parent_id' => $createdMenus['posts_management'],
                'sort_order' => 2,
            ],
            [
                'name' => 'featured_management',
                'title' => 'Vitrin Yönetimi',
                'type' => 'single',
                'route' => 'headline.index',
                'active_pattern' => 'headline.*',
                'permission' => 'view featured',
                'roles' => ['super_admin', 'admin', 'editor'],
                'parent_id' => $createdMenus['posts_management'],
                'sort_order' => 5,
            ],
            [
                'name' => 'lastminutes_management',
                'title' => 'Son Dakika Yönetimi',
                'type' => 'single',
                'route' => 'lastminutes.index',
                'active_pattern' => 'lastminutes.*',
                'permission' => 'view lastminutes',
                'roles' => ['super_admin', 'admin', 'editor'],
                'parent_id' => $createdMenus['posts_management'],
                'sort_order' => 6,
            ],
            [
                'name' => 'agencynews_management',
                'title' => 'Ajans Haberleri',
                'type' => 'single',
                'route' => 'agencynews.index',
                'active_pattern' => 'agencynews.*',
                'permission' => 'view agency_news',
                'roles' => ['super_admin', 'admin', 'editor'],
                'parent_id' => $createdMenus['posts_management'],
                'sort_order' => 7,
            ],
            // Kategori Yönetimi alt menüleri
            [
                'name' => 'categories_list',
                'title' => 'Kategori Listesi',
                'type' => 'single',
                'route' => 'categories.index',
                'active_pattern' => 'categories.index',
                'roles' => ['super_admin', 'admin'],
                'permission' => 'view categories',
                'parent_id' => $createdMenus['categories_management'],
                'sort_order' => 1,
            ],
            [
                'name' => 'categories_create',
                'title' => 'Yeni Kategori',
                'type' => 'single',
                'route' => 'categories.create',
                'active_pattern' => 'categories.create',
                'roles' => ['super_admin', 'admin'],
                'permission' => 'create categories',
                'parent_id' => $createdMenus['categories_management'],
                'sort_order' => 2,
            ],
            // Dosya Yönetimi alt menüleri
            [
                'name' => 'files_list',
                'title' => 'Dosya Listesi',
                'type' => 'single',
                'route' => 'files.index',
                'active_pattern' => 'files.index',
                'roles' => ['super_admin', 'admin', 'editor'],
                'permission' => 'view files',
                'parent_id' => $createdMenus['files_management'],
                'sort_order' => 1,
            ],
            [
                'name' => 'files_create',
                'title' => 'Dosya Yükle',
                'type' => 'single',
                'route' => 'files.create',
                'active_pattern' => 'files.create',
                'roles' => ['super_admin', 'admin', 'editor'],
                'permission' => 'create files',
                'parent_id' => $createdMenus['files_management'],
                'sort_order' => 2,
            ],
            // Yazar Yönetimi alt menüleri
            [
                'name' => 'authors_list',
                'title' => 'Yazar Listesi',
                'type' => 'single',
                'route' => 'authors.index',
                'active_pattern' => 'authors.index',
                'roles' => ['super_admin', 'admin', 'editor'],
                'permission' => 'view authors',
                'parent_id' => $createdMenus['authors_management'],
                'sort_order' => 1,
            ],
            [
                'name' => 'authors_create',
                'title' => 'Yeni Yazar',
                'type' => 'single',
                'route' => 'authors.create',
                'active_pattern' => 'authors.create',
                'roles' => ['super_admin', 'admin', 'editor'],
                'permission' => 'create authors',
                'parent_id' => $createdMenus['authors_management'],
                'sort_order' => 2,
            ],
            // Sistem Ayarları alt menüleri
            [
                'name' => 'menu_management',
                'title' => 'Menü Yönetimi',
                'type' => 'single',
                'route' => 'admin.menu.index',
                'active_pattern' => 'admin.menu.*',
                'roles' => ['super_admin', 'admin'],
                'permission' => 'manage menu',
                'parent_id' => $createdMenus['settings_management'],
                'sort_order' => 1,
            ],
            [
                'name' => 'site_settings',
                'title' => 'Site Ayarları',
                'type' => 'single',
                'route' => 'admin.settings.index',
                'active_pattern' => 'admin.settings.*',
                'roles' => ['super_admin', 'admin'],
                'permission' => 'view settings',
                'parent_id' => $createdMenus['settings_management'],
                'sort_order' => 2,
            ],
            // Bülten Yönetimi alt menüleri
            [
                'name' => 'newsletters_list',
                'title' => 'Bülten Listesi',
                'type' => 'single',
                'route' => 'newsletters.index',
                'active_pattern' => 'newsletters.index',
                'roles' => ['super_admin', 'admin', 'editor'],
                'permission' => 'view newsletters',
                'parent_id' => $createdMenus['newsletters_management'],
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'newsletters_create',
                'title' => 'Yeni Bülten',
                'type' => 'single',
                'route' => 'newsletters.create',
                'active_pattern' => 'newsletters.create',
                'roles' => ['super_admin', 'admin', 'editor'],
                'permission' => 'create newsletters',
                'parent_id' => $createdMenus['newsletters_management'],
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'newsletters_users',
                'title' => 'Abone Yönetimi',
                'type' => 'single',
                'route' => 'newsletters.users.index',
                'active_pattern' => 'newsletters.users.*',
                'roles' => ['super_admin', 'admin', 'editor'],
                'permission' => 'view newsletters',
                'parent_id' => $createdMenus['newsletters_management'],
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'newsletters_logs',
                'title' => 'Bülten Logları',
                'type' => 'single',
                'route' => 'newsletters.logs.index',
                'active_pattern' => 'newsletters.logs.*',
                'roles' => ['super_admin', 'admin', 'editor'],
                'permission' => 'view newsletters',
                'parent_id' => $createdMenus['newsletters_management'],
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'newsletters_templates',
                'title' => 'Template Yönetimi',
                'type' => 'single',
                'route' => 'newsletters.templates.index',
                'active_pattern' => 'newsletters.templates.*',
                'roles' => ['super_admin', 'admin', 'editor'],
                'permission' => 'view newsletters',
                'parent_id' => $createdMenus['newsletters_management'],
                'sort_order' => 5,
                'is_active' => true,
            ],
            // Hisse Yönetimi alt menüleri
            [
                'name' => 'stocks_management',
                'title' => 'Hisse Senetleri',
                'type' => 'single',
                'route' => 'banks.stocks.index',
                'active_pattern' => 'banks.stocks.*',
                'permission' => 'view stocks',
                'roles' => ['super_admin', 'admin', 'editor'],
                'parent_id' => $createdMenus['banks_management'],
                'sort_order' => 1,
            ],
            [
                'name' => 'stocks_create',
                'title' => 'Yeni Hisse Senedi',
                'type' => 'single',
                'route' => 'banks.stocks.create',
                'active_pattern' => 'banks.stocks.create',
                'permission' => 'create stocks',
                'roles' => ['super_admin', 'admin', 'editor'],
                'parent_id' => $createdMenus['banks_management'],
                'sort_order' => 2,
            ],
            [
                'name' => 'investor_questions_management',
                'title' => 'Yatırımcı Soruları',
                'type' => 'single',
                'route' => 'banks.investor-questions.index',
                'active_pattern' => 'banks.investor-questions.*',
                'permission' => 'view investor_questions',
                'roles' => ['super_admin', 'admin', 'editor'],
                'parent_id' => $createdMenus['banks_management'],
                'sort_order' => 3,
            ],
        ];

        // İlk seviye alt menüleri oluştur
        foreach ($subMenuItems as $item) {
            $submenu = MenuItem::updateOrCreate(
                ['name' => $item['name']], // unique key
                $item
            );
            $createdMenus[$item['name']] = $submenu->id;
        }

        // İkinci seviye alt menüleri (posts_create_menu altındakiler)
        $nestedSubMenuItems = [
            [
                'name' => 'posts_create_news_item',
                'title' => 'Haber Ekle',
                'type' => 'single',
                'route' => 'posts.create.news',
                'active_pattern' => 'posts.create.news',
                'parent_id' => $createdMenus['posts_create_menu'],
                'sort_order' => 1,
            ],
            [
                'name' => 'posts_create_gallery_item',
                'title' => 'Galeri Ekle',
                'type' => 'single',
                'route' => 'posts.create.gallery',
                'active_pattern' => 'posts.create.gallery',
                'parent_id' => $createdMenus['posts_create_menu'],
                'sort_order' => 2,
            ],
            [
                'name' => 'posts_create_video_item',
                'title' => 'Video Ekle',
                'type' => 'single',
                'route' => 'posts.create.video',
                'active_pattern' => 'posts.create.video',
                'parent_id' => $createdMenus['posts_create_menu'],
                'sort_order' => 3,
            ],
        ];

        foreach ($nestedSubMenuItems as $item) {
            MenuItem::updateOrCreate(
                ['name' => $item['name']], // unique key
                $item
            );
        }

    }
}
