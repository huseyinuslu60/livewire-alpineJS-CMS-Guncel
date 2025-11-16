<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Sadece permission alanı boş olan menü öğelerini güncelle
        $menuItems = \App\Models\MenuItem::whereNull('permission')->get();

        foreach ($menuItems as $item) {
            $permission = null;

            // Menü başlığına göre permission belirle
            switch ($item->title) {
                case 'Kullanıcı Yönetimi':
                    $permission = 'view users';
                    break;
                case 'Kullanıcı Listesi':
                    $permission = 'view users';
                    break;
                case 'Yeni Kullanıcı':
                    $permission = 'create users';
                    break;
                case 'Makale Yönetimi':
                    $permission = 'view articles';
                    break;
                case 'Makale Listesi':
                    $permission = 'view articles';
                    break;
                case 'Yeni Makale':
                    $permission = 'create articles';
                    break;
                case 'Haber Yönetimi':
                    $permission = 'view posts';
                    break;
                case 'Haber Listesi':
                    $permission = 'view posts';
                    break;
                case 'Yeni Haber':
                    $permission = 'create posts';
                    break;
                case 'Kategori Yönetimi':
                    $permission = 'view categories';
                    break;
                case 'Kategori Listesi':
                    $permission = 'view categories';
                    break;
                case 'Yeni Kategori':
                    $permission = 'create categories';
                    break;
                case 'Dosya Yönetimi':
                    $permission = 'view files';
                    break;
                case 'Dosya Listesi':
                    $permission = 'view files';
                    break;
                case 'Dosya Yükle':
                    $permission = 'create files';
                    break;
                case 'Yazar Yönetimi':
                    $permission = 'view authors';
                    break;
                case 'Yazar Listesi':
                    $permission = 'view authors';
                    break;
                case 'Yeni Yazar':
                    $permission = 'create authors';
                    break;
                case 'Yorum Yönetimi':
                    $permission = 'view comments';
                    break;
                case 'Rol Yönetimi':
                    $permission = 'view roles';
                    break;
                case 'Sistem Logları':
                    $permission = 'view logs';
                    break;
                case 'Modül Yönetimi':
                    $permission = 'view settings';
                    break;
            }

            if ($permission) {
                $item->update(['permission' => $permission]);
            }
        }

        // Eğer hiç menü öğesi yoksa, temel menü yapısını oluştur
        if (\App\Models\MenuItem::count() === 0) {
            $this->createDefaultMenuItems();
        }
    }

    /**
     * Varsayılan menü öğelerini oluştur
     */
    private function createDefaultMenuItems(): void
    {
        // Temel menü öğelerini oluştur (sadece gerekli olanlar)
        // Not: Tüm menü öğeleri MenuItemsSeeder'da oluşturuluyor, burada oluşturulmuyor
        // Bu metod artık kullanılmıyor çünkü MenuItemsSeeder tüm menüleri oluşturuyor
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Migration'ı geri alırken permission alanlarını temizle
        \App\Models\MenuItem::whereNotNull('permission')->update(['permission' => null]);
    }
};
