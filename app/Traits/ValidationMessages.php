<?php

namespace App\Traits;

trait ValidationMessages
{
    /**
     * Standart validation mesajları
     */
    protected function getValidationMessages(): array
    {
        return [
            // Genel mesajlar
            'required' => ':attribute alanı zorunludur.',
            'string' => ':attribute metin formatında olmalıdır.',
            'integer' => ':attribute sayı formatında olmalıdır.',
            'boolean' => ':attribute doğru/yanlış formatında olmalıdır.',
            'email' => ':attribute geçerli bir e-posta adresi olmalıdır.',
            'url' => ':attribute geçerli bir URL olmalıdır.',
            'date' => ':attribute geçerli bir tarih olmalıdır.',
            'array' => ':attribute dizi formatında olmalıdır.',
            'file' => ':attribute geçerli bir dosya olmalıdır.',
            'image' => ':attribute geçerli bir resim dosyası olmalıdır.',

            // Uzunluk mesajları
            'max.string' => ':attribute en fazla :max karakter olabilir.',
            'max.file' => ':attribute boyutu en fazla :max KB olabilir.',
            'min.string' => ':attribute en az :min karakter olmalıdır.',
            'min.integer' => ':attribute en az :min olmalıdır.',

            // Benzersizlik mesajları
            'unique' => ':attribute zaten kullanılıyor.',
            'exists' => 'Seçilen :attribute geçersiz.',

            // Format mesajları
            'in' => 'Geçersiz :attribute seçimi.',
            'mimes' => ':attribute formatı :values olmalıdır.',
            'confirmed' => ':attribute onayı eşleşmiyor.',

            // Özel alan mesajları
            'name.required' => 'Ad alanı zorunludur.',
            'name.max' => 'Ad en fazla :max karakter olabilir.',
            'title.required' => 'Başlık alanı zorunludur.',
            'title.max' => 'Başlık en fazla :max karakter olabilir.',
            'email.required' => 'E-posta alanı zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'email.unique' => 'Bu e-posta adresi zaten kullanılıyor.',
            'password.required' => 'Şifre alanı zorunludur.',
            'password.min' => 'Şifre en az :min karakter olmalıdır.',
            'password.confirmed' => 'Şifre onayı eşleşmiyor.',
            'slug.unique' => 'Bu URL adresi zaten kullanılıyor.',
            'status.required' => 'Durum seçimi zorunludur.',
            'status.in' => 'Geçersiz durum seçimi.',
            'type.required' => 'Tip seçimi zorunludur.',
            'type.in' => 'Geçersiz tip seçimi.',
            'author_id.required' => 'Yazar seçimi zorunludur.',
            'author_id.exists' => 'Seçilen yazar geçersiz.',
            'category_id.required' => 'Kategori seçimi zorunludur.',
            'category_id.exists' => 'Seçilen kategori geçersiz.',
            'role_ids.required' => 'En az bir rol seçimi zorunludur.',
            'role_ids.array' => 'Roller dizi formatında olmalıdır.',
            'role_ids.min' => 'En az bir rol seçmelisiniz.',
            'role_ids.*.exists' => 'Seçilen rollerden biri geçersiz.',
            'files.*.required' => 'En az bir dosya seçmelisiniz.',
            'files.*.file' => 'Seçilen dosya geçerli değil.',
            'files.*.max' => 'Dosya boyutu :max KB\'dan büyük olamaz.',
            'image.*.image' => 'Yüklenen dosya resim formatında olmalıdır.',
            'image.*.mimes' => 'Resim formatı :values olmalıdır.',
            'image.*.max' => 'Resim boyutu en fazla :max KB olabilir.',
        ];
    }

    /**
     * Context-aware attribute isimleri
     */
    protected function getContextualAttributeNames(): array
    {
        return [
            'user' => [
                'name' => 'Kullanıcı',
                'email' => 'E-posta',
                'password' => 'Şifre',
                'password_confirmation' => 'Şifre Onayı',
                'role_ids' => 'Roller',
            ],
            'role' => [
                'name' => 'Rol',
                'display_name' => 'Görünen Ad',
                'description' => 'Açıklama',
            ],
            'category' => [
                'name' => 'Kategori',
                'slug' => 'URL Adresi',
                'description' => 'Açıklama',
                'weight' => 'Sıralama',
                'show_in_menu' => 'Menüde Göster',
                'parent_id' => 'Üst Kategori',
            ],
            'article' => [
                'title' => 'Makale',
                'summary' => 'Özet',
                'article_text' => 'Makale İçeriği',
                'author_id' => 'Yazar',
                'status' => 'Durum',
                'show_on_mainpage' => 'Ana Sayfada Göster',
                'is_commentable' => 'Yorumlara Açık',
                'published_at' => 'Yayın Tarihi',
            ],
            'author' => [
                'title' => 'Yazar',
                'bio' => 'Biyografi',
                'user_id' => 'Kullanıcı',
                'image' => 'Resim',
                'twitter' => 'Twitter',
                'linkedin' => 'LinkedIn',
                'facebook' => 'Facebook',
                'instagram' => 'Instagram',
                'website' => 'Web Sitesi',
                'weight' => 'Sıralama',
                'show_on_mainpage' => 'Ana Sayfada Göster',
                'status' => 'Durum',
            ],
            'post' => [
                'title' => 'Yazı',
                'summary' => 'Özet',
                'content' => 'İçerik',
                'status' => 'Durum',
                'type' => 'Tip',
                'show_on_mainpage' => 'Ana Sayfada Göster',
                'is_commentable' => 'Yorumlara Açık',
                'published_at' => 'Yayın Tarihi',
            ],
            'file' => [
                'name' => 'Dosya',
                'alt_text' => 'Alt Metin',
                'caption' => 'Açıklama',
                'files' => 'Dosyalar',
            ],
        ];
    }

    /**
     * Eski sistem için geriye dönük uyumluluk
     */
    protected function getAttributeNames(): array
    {
        return [
            'name' => 'Ad',
            'title' => 'Başlık',
            'email' => 'E-posta',
            'user' => 'Kullanıcı',
            'category' => 'Kategori',
            'author' => 'Yazar',
            'article' => 'Makale',
            'post' => 'Haber',
            'password' => 'Şifre',
            'password_confirmation' => 'Şifre Onayı',
            'slug' => 'URL Adresi',
            'status' => 'Durum',
            'type' => 'Tip',
            'author_id' => 'Yazar',
            'category_id' => 'Kategori',
            'role_ids' => 'Roller',
            'files' => 'Dosyalar',
            'image' => 'Resim',
            'alt_text' => 'Alt Metin',
            'caption' => 'Açıklama',
            'summary' => 'Özet',
            'content' => 'İçerik',
            'article_text' => 'Makale İçeriği',
            'meta_title' => 'Meta Başlık',
            'meta_description' => 'Meta Açıklama',
            'meta_keywords' => 'Meta Anahtar Kelimeler',
            'weight' => 'Sıralama',
            'show_in_menu' => 'Menüde Göster',
            'show_on_mainpage' => 'Ana Sayfada Göster',
            'is_commentable' => 'Yorumlara Açık',
            'published_at' => 'Yayın Tarihi',
            'parent_id' => 'Üst Kategori',
            'display_name' => 'Görünen Ad',
            'description' => 'Açıklama',
            'bio' => 'Biyografi',
            'twitter' => 'Twitter',
            'linkedin' => 'LinkedIn',
            'facebook' => 'Facebook',
            'instagram' => 'Instagram',
            'website' => 'Web Sitesi',
            'role' => 'Rol',
            'permission' => 'Yetki',
            'file' => 'Dosya',
        ];
    }

    /**
     * Success mesajları için standart mesajlar
     */
    protected function getSuccessMessages(): array
    {
        return [
            'created' => ':attribute başarıyla oluşturuldu.',
            'updated' => ':attribute başarıyla güncellendi.',
            'deleted' => ':attribute başarıyla silindi.',
            'saved' => ':attribute başarıyla kaydedildi.',
            'uploaded' => ':attribute başarıyla yüklendi.',
            'published' => ':attribute başarıyla yayınlandı.',
            'unpublished' => ':attribute yayından kaldırıldı.',
            'activated' => ':attribute başarıyla aktifleştirildi.',
            'deactivated' => ':attribute başarıyla deaktifleştirildi.',
            'restored' => ':attribute başarıyla geri yüklendi.',
            'duplicated' => ':attribute başarıyla kopyalandı.',
            'moved' => ':attribute başarıyla taşındı.',
            'sorted' => ':attribute sıralaması güncellendi.',
            'imported' => ':attribute başarıyla içe aktarıldı.',
            'exported' => ':attribute başarıyla dışa aktarıldı.',
        ];
    }

    /**
     * Context-aware validation mesajları
     */
    protected function getContextualValidationMessages(): array
    {
        return [
            'user' => [
                'name.required' => 'Kullanıcı adı zorunludur.',
                'name.max' => 'Kullanıcı adı en fazla :max karakter olabilir.',
                'email.required' => 'E-posta alanı zorunludur.',
                'email.email' => 'Geçerli bir e-posta adresi giriniz.',
                'email.unique' => 'Bu e-posta adresi zaten kullanılıyor.',
                'password.required' => 'Şifre alanı zorunludur.',
                'password.min' => 'Şifre en az :min karakter olmalıdır.',
                'password.confirmed' => 'Şifre onayı eşleşmiyor.',
                'role_ids.required' => 'En az bir rol seçimi zorunludur.',
                'role_ids.array' => 'Roller dizi formatında olmalıdır.',
                'role_ids.min' => 'En az bir rol seçmelisiniz.',
                'role_ids.*.exists' => 'Seçilen rollerden biri geçersiz.',
            ],
            'role' => [
                'name.required' => 'Rol adı zorunludur.',
                'name.unique' => 'Bu rol adı zaten kullanılıyor.',
                'name.max' => 'Rol adı en fazla :max karakter olabilir.',
                'display_name.required' => 'Görünen ad zorunludur.',
                'display_name.max' => 'Görünen ad en fazla :max karakter olabilir.',
                'description.string' => 'Açıklama metin formatında olmalıdır.',
            ],
            'category' => [
                'name.required' => 'Kategori adı zorunludur.',
                'name.max' => 'Kategori adı en fazla :max karakter olabilir.',
                'slug.unique' => 'Bu URL adresi zaten kullanılıyor.',
                'slug.max' => 'URL adresi en fazla :max karakter olabilir.',
                'description.string' => 'Açıklama metin formatında olmalıdır.',
                'weight.integer' => 'Sıralama sayı formatında olmalıdır.',
                'weight.min' => 'Sıralama en az :min olmalıdır.',
                'parent_id.exists' => 'Seçilen üst kategori geçersiz.',
            ],
            'article' => [
                'title.required' => 'Makale başlığı zorunludur.',
                'title.max' => 'Makale başlığı en fazla :max karakter olabilir.',
                'summary.max' => 'Makale özeti en fazla :max karakter olabilir.',
                'article_text.required' => 'Makale içeriği zorunludur.',
                'author_id.required' => 'Yazar seçimi zorunludur.',
                'author_id.exists' => 'Seçilen yazar geçersiz.',
                'status.required' => 'Durum seçimi zorunludur.',
                'status.in' => 'Geçersiz durum seçimi.',
                'published_at.date' => 'Geçersiz yayın tarihi formatı.',
            ],
            'author' => [
                'title.required' => 'Yazar adı zorunludur.',
                'title.max' => 'Yazar adı en fazla :max karakter olabilir.',
                'bio.string' => 'Biyografi metin formatında olmalıdır.',
                'user_id.required' => 'Kullanıcı seçimi zorunludur.',
                'user_id.exists' => 'Seçilen kullanıcı geçersiz.',
                'user_id.unique' => 'Bu kullanıcı zaten yazar olarak kayıtlı.',
                'image.image' => 'Yüklenen dosya resim formatında olmalıdır.',
                'image.mimes' => 'Resim formatı :values olmalıdır.',
                'image.max' => 'Resim boyutu en fazla :max KB olabilir.',
                'twitter.max' => 'Twitter kullanıcı adı en fazla :max karakter olabilir.',
                'linkedin.max' => 'LinkedIn profili en fazla :max karakter olabilir.',
                'facebook.max' => 'Facebook profili en fazla :max karakter olabilir.',
                'instagram.max' => 'Instagram kullanıcı adı en fazla :max karakter olabilir.',
                'website.url' => 'Web sitesi geçerli bir URL formatında olmalıdır.',
                'website.max' => 'Web sitesi URL\'si en fazla :max karakter olabilir.',
                'weight.integer' => 'Sıralama sayı formatında olmalıdır.',
                'weight.min' => 'Sıralama en az :min olmalıdır.',
            ],
            'post' => [
                'title.required' => 'Yazı başlığı zorunludur.',
                'title.max' => 'Yazı başlığı en fazla :max karakter olabilir.',
                'summary.max' => 'Yazı özeti en fazla :max karakter olabilir.',
                'content.required' => 'Yazı içeriği zorunludur.',
                'status.required' => 'Durum seçimi zorunludur.',
                'status.in' => 'Geçersiz durum seçimi.',
                'type.required' => 'Tip seçimi zorunludur.',
                'type.in' => 'Geçersiz tip seçimi.',
                'published_at.date' => 'Geçersiz yayın tarihi formatı.',
            ],
            'file' => [
                'name.required' => 'Dosya adı zorunludur.',
                'name.max' => 'Dosya adı en fazla :max karakter olabilir.',
                'alt_text.max' => 'Alt metin en fazla :max karakter olabilir.',
                'caption.max' => 'Açıklama en fazla :max karakter olabilir.',
                'files.*.required' => 'En az bir dosya seçmelisiniz.',
                'files.*.file' => 'Seçilen dosya geçerli değil.',
                'files.*.max' => 'Dosya boyutu :max KB\'dan büyük olamaz.',
            ],
        ];
    }

    /**
     * Context-aware validation mesajı oluştur
     */
    protected function createContextualValidationMessage(string $field, string $rule, string $context, array $parameters = []): string
    {
        $contextualMessages = $this->getContextualValidationMessages();
        $key = $field.'.'.$rule;

        if (isset($contextualMessages[$context][$key])) {
            $message = $contextualMessages[$context][$key];
        } else {
            // Fallback: genel mesajlar
            $generalMessages = $this->getValidationMessages();
            $message = $generalMessages[$rule] ?? ':attribute alanı geçersiz.';

            // Context-aware attribute ismi
            $contextualAttributes = $this->getContextualAttributeNames();
            if (isset($contextualAttributes[$context][$field])) {
                $attributeName = $contextualAttributes[$context][$field];
            } else {
                $attributeName = $field;
            }
            $message = str_replace(':attribute', $attributeName, $message);
        }

        // Parametreleri değiştir
        foreach ($parameters as $key => $value) {
            $message = str_replace(':'.$key, $value, $message);
        }

        return $message;
    }

    /**
     * Context-aware success mesajı oluştur
     */
    protected function createContextualSuccessMessage(string $action, string $field, string $context): string
    {
        $messages = $this->getSuccessMessages();
        $contextualAttributes = $this->getContextualAttributeNames();

        $message = $messages[$action] ?? ':attribute işlemi başarıyla tamamlandı.';

        if (isset($contextualAttributes[$context][$field])) {
            $attributeName = $contextualAttributes[$context][$field];
        } else {
            // Fallback: eski sistem
            $attributeNames = $this->getAttributeNames();
            $attributeName = $attributeNames[$field] ?? $field;
        }

        return str_replace(':attribute', $attributeName, $message);
    }

    /**
     * Success mesajı oluştur (geriye dönük uyumluluk)
     */
    protected function createSuccessMessage(string $action, ?string $attribute = null): string
    {
        $messages = $this->getSuccessMessages();
        $message = $messages[$action] ?? ':attribute işlemi başarıyla tamamlandı.';

        if ($attribute) {
            $attributeNames = $this->getAttributeNames();
            $attributeName = $attributeNames[$attribute] ?? $attribute;

            return str_replace(':attribute', $attributeName, $message);
        }

        return str_replace(':attribute', 'İşlem', $message);
    }
}
