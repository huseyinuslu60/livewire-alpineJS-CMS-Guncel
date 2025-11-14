<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            [
                'name' => 'articles',
                'display_name' => 'Makaleler',
                'description' => 'Makale yönetimi modülü - makale oluşturma, düzenleme ve yayınlama',
                'version' => '1.0.0',
                'is_active' => true,
                'icon' => 'fas fa-file-alt',
                'route_prefix' => 'articles',
                'permissions' => ['articles.create', 'articles.edit', 'articles.delete', 'articles.view'],
                'sort_order' => 1,
            ],
            [
                'name' => 'categories',
                'display_name' => 'Kategoriler',
                'description' => 'Kategori yönetimi modülü - makale kategorilerini organize etme',
                'version' => '1.0.0',
                'is_active' => true,
                'icon' => 'fas fa-folder',
                'route_prefix' => 'categories',
                'permissions' => ['categories.create', 'categories.edit', 'categories.delete', 'categories.view'],
                'sort_order' => 2,
            ],
            [
                'name' => 'roles',
                'display_name' => 'Rol Yönetimi',
                'description' => 'Kullanıcı rol ve izin yönetimi modülü',
                'version' => '1.0.0',
                'is_active' => true,
                'icon' => 'fas fa-shield-alt',
                'route_prefix' => 'roles',
                'permissions' => ['roles.create', 'roles.edit', 'roles.delete', 'roles.view'],
                'sort_order' => 3,
            ],
            [
                'name' => 'user',
                'display_name' => 'Kullanıcılar',
                'description' => 'Kullanıcı yönetimi modülü - kullanıcı ekleme, düzenleme ve yönetme',
                'version' => '1.0.0',
                'is_active' => true,
                'icon' => 'fas fa-users',
                'route_prefix' => 'users',
                'permissions' => ['user.create', 'user.edit', 'user.delete', 'user.view'],
                'sort_order' => 4,
            ],
            [
                'name' => 'comments',
                'display_name' => 'Yorum Yönetimi',
                'description' => 'Makale yorumlarını yönetme modülü',
                'version' => '1.0.0',
                'is_active' => true, // Aktif hale getir
                'icon' => 'fas fa-comments',
                'route_prefix' => 'comments',
                'permissions' => ['comments.view', 'comments.delete', 'comments.approve'],
                'sort_order' => 6,
            ],
            [
                'name' => 'authors',
                'display_name' => 'Yazar Yönetimi',
                'description' => 'Yazar profillerini yönetme modülü - yazar ekleme, düzenleme ve yönetme',
                'version' => '1.0.0',
                'is_active' => true,
                'icon' => 'fas fa-user-plus',
                'route_prefix' => 'authors',
                'permissions' => ['authors.create', 'authors.edit', 'authors.delete', 'authors.view'],
                'sort_order' => 7,
            ],
            [
                'name' => 'files',
                'display_name' => 'Dosya Yönetimi',
                'description' => 'Dosya yükleme ve yönetimi modülü - medya dosyalarını yönetme',
                'version' => '1.0.0',
                'is_active' => true,
                'icon' => 'fas fa-file-upload',
                'route_prefix' => 'files',
                'permissions' => ['files.upload', 'files.delete', 'files.view'],
                'sort_order' => 8,
            ],
            [
                'name' => 'posts',
                'display_name' => 'Haberler',
                'description' => 'Haber yönetimi modülü - haber oluşturma, düzenleme ve yayınlama',
                'version' => '1.0.0',
                'is_active' => true,
                'icon' => 'fas fa-newspaper',
                'route_prefix' => 'posts',
                'permissions' => ['posts.create', 'posts.edit', 'posts.delete', 'posts.view'],
                'sort_order' => 9,
            ],
            [
                'name' => 'logs',
                'display_name' => 'Sistem Logları',
                'description' => 'Sistem log yönetimi modülü - kullanıcı aktivitelerini ve sistem işlemlerini takip etme',
                'version' => '1.0.0',
                'is_active' => true,
                'icon' => 'fas fa-clipboard-list',
                'route_prefix' => 'logs',
                'permissions' => ['logs.view', 'logs.delete'],
                'sort_order' => 10,
            ],
            [
                'name' => 'headline',
                'display_name' => 'Vitrin Yönetimi',
                'description' => 'Vitrin yönetimi modülü - manşet, sürmanşet ve öne çıkanlar alanlarını yönetme',
                'version' => '1.0.0',
                'is_active' => true,
                'icon' => 'fas fa-star',
                'route_prefix' => 'admin',
                'permissions' => ['manage-featured', 'featured.view', 'featured.edit', 'featured.delete'],
                'sort_order' => 11,
            ],
            [
                'name' => 'lastminutes',
                'display_name' => 'Son Dakika Yönetimi',
                'description' => 'Son dakika haberleri yönetimi modülü - acil haberlerin hızlı yayınlanması ve yönetimi',
                'version' => '1.0.0',
                'is_active' => true,
                'icon' => 'fas fa-bolt',
                'route_prefix' => 'lastminutes',
                'permissions' => ['view lastminutes', 'create lastminutes', 'edit lastminutes', 'delete lastminutes'],
                'sort_order' => 12,
            ],
            [
                'name' => 'settings',
                'display_name' => 'Sistem Ayarları',
                'description' => 'Sistem ayarları yönetimi modülü - site konfigürasyonu ve menü yönetimi',
                'version' => '1.0.0',
                'is_active' => true,
                'icon' => 'fas fa-cog',
                'route_prefix' => 'admin',
                'permissions' => ['view settings', 'edit settings', 'manage menu'],
                'sort_order' => 13,
            ],
            [
                'name' => 'agency_news',
                'display_name' => 'Ajans Haberleri',
                'description' => 'Ajans haberleri yönetimi modülü - dış ajanslardan gelen haberleri yönetme',
                'version' => '1.0.0',
                'is_active' => true,
                'icon' => 'fas fa-newspaper',
                'route_prefix' => 'agencynews',
                'permissions' => ['view agency_news', 'delete agency_news', 'publish agency_news'],
                'sort_order' => 14,
            ],
            [
                'name' => 'modules',
                'display_name' => 'Modül Yönetimi',
                'description' => 'Sistem modüllerini yönetme - modül aktivasyonu ve deaktivasyonu',
                'version' => '1.0.0',
                'is_active' => true,
                'icon' => 'fas fa-puzzle-piece',
                'route_prefix' => 'admin',
                'permissions' => ['view modules', 'edit modules', 'activate modules'],
                'sort_order' => 15,
            ],
            [
                'name' => 'banks',
                'display_name' => 'Hisse Yönetimi',
                'description' => 'Hisse senedi ve yatırımcı soruları yönetimi modülü - hisse bilgileri ve soru-cevap sistemi',
                'version' => '1.0.0',
                'is_active' => true,
                'icon' => 'fas fa-chart-line',
                'route_prefix' => 'banks',
                'permissions' => ['view stocks', 'create stocks', 'edit stocks', 'delete stocks', 'view investor_questions', 'edit investor_questions', 'delete investor_questions'],
                'sort_order' => 16,
            ],
            [
                'name' => 'newsletters',
                'display_name' => 'Bülten Yönetimi',
                'description' => 'Newsletter ve bülten yönetimi modülü - bülten oluşturma, gönderme ve takip',
                'version' => '1.0.0',
                'is_active' => true,
                'icon' => 'fas fa-envelope',
                'route_prefix' => 'newsletters',
                'permissions' => ['newsletters.create', 'newsletters.edit', 'newsletters.delete', 'newsletters.view'],
                'sort_order' => 13,
            ],
        ];

        foreach ($modules as $module) {
            Module::updateOrCreate(
                ['name' => $module['name']],
                $module
            );
        }
    }
}
