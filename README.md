# bg-laravel Admin Platform

[![CI](https://github.com/huseyinuslu60/livewire-alpineJS-CMS-Guncel/actions/workflows/ci.yml/badge.svg)](https://github.com/huseyinuslu60/livewire-alpineJS-CMS-Guncel/actions/workflows/ci.yml)

Modern, modüler ve güvenli bir içerik yönetimi platformu. Laravel 12 (PHP 8.2), Livewire 3, Tailwind v4, Vite 7 ve Alpine.js 3 üzerine kurulu; editor lazy-load, güvenli dosya yükleme, `spot_data` tabanlı görsel düzenleme ve performans odaklı mimari içerir.

Hızlı bağlantılar:
- Geliştirici Rehberi: `docs/development-guide.md`
- Kurulum Kısayol: `composer install` → `npm install` → `.env` → `php artisan key:generate` → `php artisan migrate:fresh --seed` → `npm run dev`
- Önemli ENV: `FILES_MAX_SIZE_KB`, `IMAGE_DOWNLOAD_ALLOWED_HOSTS`, `LOG_VERBOSE`

Öne çıkanlar:
- Editor Lazy-Load (jQuery + Trumbowyg ayrı chunk, yalnızca `[data-editor]` olan sayfalarda yüklenir)
- Güvenlik: XSS Sanitizer, SSRF same-origin guard + whitelist, SecureFileUpload (MIME/extension/finfo/evil-content)
- Performans: Composite indexler, batch gallery update (N+1 yok), Vite manualChunks
- Mimari: Nwidart Modules, Service katmanı, Livewire + Alpine lifecycle yönetimi

Modern Laravel 12 tabanlı, tam modüler haber ve içerik yönetim sistemi. Livewire 3 ve Alpine.js ile geliştirilmiş, kullanıcı dostu admin paneli ve güçlü özellikler sunar.

> **Not:** Bu proje aktif olarak geliştirilmektedir. Son güncellemeler için [commits](https://github.com/huseyinuslu60/livewire-alpineJS-CMS-Guncel/commits/main) sayfasını kontrol edebilirsiniz.

## 🎯 Özellikler

### 🧩 Modüler Mimari
- **Tam modüler yapı** - `nwidart/laravel-modules` ile bağımsız modül geliştirme
- **16 aktif modül** - Her modül kendi route, controller, view ve migration'larına sahip
- **Kolay genişletilebilirlik** - Yeni modüller kolayca eklenebilir

### ⚡ Modern Teknoloji Stack
- **Laravel 12** - En son Laravel sürümü ile güçlü backend
- **Livewire 3** - Sunucu tarafı reactive component'ler
- **Alpine.js** - Hafif ve güçlü JavaScript framework
- **Tailwind CSS 4** - Modern ve responsive UI tasarımı
- **PostgreSQL** - Güçlü ve ölçeklenebilir veritabanı
- **Vite** - Hızlı frontend build tool

### 🛠️ Admin Paneli Özellikleri
- **Rol tabanlı erişim kontrolü** - Editör, Admin ve özel roller
- **Kullanıcı yönetimi** - Kapsamlı kullanıcı ve yetki yönetimi
- **İçerik yönetimi** - Haber, makale ve kategori yönetimi
- **Dosya yönetimi** - Güvenli dosya yükleme ve yönetim sistemi
- **Manşet yönetimi** - Drag-drop ile sıralanabilir manşet/sürmanşet
- **Yorum sistemi** - İçerik yorumları ve moderasyon
- **Log yönetimi** - Sistem aktivite logları

### 📬 İletişim ve Bildirim
- **Bülten sistemi** - E-posta bülteni yönetimi ve abonelik
- **Ajans haberleri** - Dış kaynak haber entegrasyonu
- **Son dakika haberleri** - Acil haber yönetimi

### 🏦 Ek Özellikler
- **Banka yönetimi** - Banka bilgileri ve entegrasyonları
- **Ayarlar modülü** - Sistem geneli ayar yönetimi
- **Yazar yönetimi** - İçerik yazarları ve profilleri

### 🧱 Geliştirme Araçları
- **CI/CD entegrasyonu** - GitHub Actions ile otomatik test ve deploy
- **Code Quality** - Laravel Pint, PHPStan ile kod kalitesi
- **Test Coverage** - Pest/PHPUnit ile kapsamlı testler
- **Type Safety** - Livewire component'lerinde type declarations ve PHPDoc
- **Docker desteği** - Kolay geliştirme ortamı kurulumu
- **Editor Support** - Trumbowyg WYSIWYG editör entegrasyonu
- **Drag & Drop** - SortableJS ile sıralanabilir listeler

## 📦 Modüller

Proje aşağıdaki modülleri içermektedir:

| Modül | Açıklama |
|-------|----------|
| **Articles** | Makale yönetimi ve içerik oluşturma |
| **Authors** | Yazar profilleri ve yönetimi |
| **AgencyNews** | Ajans haberleri entegrasyonu |
| **Banks** | Banka bilgileri ve yönetimi |
| **Categories** | Kategori yönetimi ve hiyerarşisi |
| **Comments** | Yorum sistemi ve moderasyon |
| **Files** | Dosya yükleme ve yönetim sistemi |
| **Headline** | Manşet ve sürmanşet yönetimi |
| **Lastminutes** | Son dakika haberleri |
| **Logs** | Sistem logları ve aktivite takibi |
| **Newsletters** | E-posta bülteni yönetimi |
| **Posts** | Haber ve içerik yönetimi |
| **Roles** | Rol ve yetki yönetimi |
| **Settings** | Sistem ayarları |
| **User** | Kullanıcı yönetimi |
| **Users** | Kullanıcı işlemleri |

## 🚀 Kurulum

### Gereksinimler

- **PHP** >= 8.2
- **Composer** >= 2.0
- **Node.js** >= 18.x ve npm
- **PostgreSQL** >= 15
- **Redis** (opsiyonel, önerilir)
- **Git** (projeyi klonlamak için)

### Adım Adım Kurulum

1. **Projeyi klonlayın**
   ```bash
   git clone https://github.com/huseyinuslu60/livewire-alpineJS-CMS-Guncel.git
   cd livewire-alpineJS-CMS-Guncel/bg-laravel
   ```

2. **Bağımlılıkları yükleyin**
   ```bash
   composer install
   npm ci
   ```

3. **Ortam değişkenlerini ayarlayın**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   
   `.env` dosyasında veritabanı ve diğer ayarları yapılandırın:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=your_database
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

4. **Veritabanını oluşturun ve seed edin**
   ```bash
   php artisan migrate --seed
   ```

5. **Frontend asset'lerini build edin**
   ```bash
   # Development
   npm run dev
   
   # Production
   npm run build
   ```

6. **Uygulamayı başlatın**
   ```bash
   php artisan serve
   ```

   Uygulama `http://localhost:8000` adresinde çalışacaktır.

### 🐳 Docker ile Kurulum

Docker kullanarak daha kolay kurulum:

1. **Docker Compose ile başlatın**
   ```bash
   docker-compose up -d
   ```

2. **Container içinde bağımlılıkları yükleyin**
   ```bash
   docker-compose exec app composer install
   docker-compose exec app npm ci
   ```

3. **Ortam değişkenlerini ayarlayın**
   ```bash
   docker-compose exec app cp .env.example .env
   docker-compose exec app php artisan key:generate
   ```

4. **Veritabanını migrate edin**
   ```bash
   docker-compose exec app php artisan migrate --seed
   ```

5. **Asset'leri build edin**
   ```bash
   docker-compose exec app npm run build
   ```

   Uygulama `http://localhost:8000` adresinde çalışacaktır.

## 🔧 Geliştirme

### Geliştirme Ortamı

Geliştirme için tüm servisleri aynı anda başlatmak:

```bash
# Composer script ile (server, queue, vite) - Önerilen
composer dev

# Veya ayrı ayrı terminal'lerde
php artisan serve          # Laravel development server
php artisan queue:listen   # Queue worker
npm run dev                # Vite dev server (hot reload)
```

**Not:** `composer dev` komutu tüm servisleri otomatik olarak başlatır ve renkli çıktılar gösterir.

### Code Quality

```bash
# PHP Code Style Fixer
./vendor/bin/pint
# veya
composer format

# Statik Analiz
./vendor/bin/phpstan analyse
# veya
composer analyse

# Testler
php artisan test
# veya
composer test
```

### Asset Build

```bash
# Development (hot reload)
npm run dev

# Production build
npm run build
```

### Yeni Modül Oluşturma

Yeni bir modül oluşturmak için:

```bash
php artisan module:make ModuleName
```

Bu komut aşağıdaki yapıyı otomatik olarak oluşturur:
- Controller'lar
- Livewire component'leri
- Model'ler
- Migration'lar
- View'lar
- Route'lar
- Test dosyaları

Modül oluşturulduktan sonra `Modules/ModuleName` dizininde çalışmaya başlayabilirsiniz.

## 🧾 Test & Kalite Kontrolleri

Proje aşağıdaki otomatik kalite kontrol süreçlerini kullanır:

| Kontrol | Araç | Komut |
|---------|------|-------|
| Kod stili | Laravel Pint | `composer format` |
| Statik analiz | PHPStan (Level 5) | `composer analyse` |
| Testler | Pest/PHPUnit | `composer test` |
| Veritabanı | PostgreSQL 15+ | CI ortamında test edilir |
| Frontend build | Vite | `npm run build` |

### Test Coverage

Test coverage raporu oluşturmak için:

```bash
php artisan test --coverage
```

Coverage raporu `coverage/html/index.html` dosyasında görüntülenebilir.

## 📚 Dokümantasyon

### Modül Yapısı

Her modül aşağıdaki yapıya sahiptir:

```
Modules/
  └── ModuleName/
      ├── app/
      │   ├── Http/
      │   │   ├── Controllers/
      │   │   └── Livewire/
      │   ├── Models/
      │   └── Policies/
      ├── config/
      ├── database/
      │   ├── migrations/
      │   └── seeders/
      ├── resources/
      │   ├── views/
      │   ├── js/
      │   └── scss/
      ├── routes/
      │   ├── web.php
      │   └── api.php
      └── tests/
```

### Route Yapısı

- **Admin Routes**: `/admin/*` prefix'i ile admin paneli route'ları
- **API Routes**: `/api/*` prefix'i ile API endpoint'leri
- **Web Routes**: Genel web route'ları

### Yetkilendirme

Proje `spatie/laravel-permission` paketini kullanır:

- **Roller**: Admin, Editor, User vb.
- **İzinler**: Modül bazlı izin yönetimi
- **Policy'ler**: Model bazlı yetkilendirme

## 🛡️ Güvenlik

Proje aşağıdaki güvenlik önlemlerini içerir:

- **CSRF Protection** - Tüm formlarda CSRF koruması
- **XSS Protection** - Input sanitization ve output escaping
- **SQL Injection Protection** - Eloquent ORM kullanımı ile parametreli sorgular
- **File Upload Security** - Güvenli dosya yükleme kontrolleri ve validasyon
- **Role-based Access Control** - Rol tabanlı erişim kontrolü
- **Policy-based Authorization** - Model bazlı yetkilendirme
- **Secure File Storage** - Private ve public dosya yönetimi

## 🤝 Katkıda Bulunma

Projeye katkıda bulunmak için:

1. Fork edin
2. Feature branch oluşturun (`git checkout -b feature/amazing-feature`)
3. Değişikliklerinizi commit edin (`git commit -m 'Add amazing feature'`)
4. Branch'inizi push edin (`git push origin feature/amazing-feature`)
5. Pull Request oluşturun

### Katkı Kuralları

- Kod standartlarına uyun (Laravel Pint)
- Test yazın
- Dokümantasyonu güncelleyin
- Açıklayıcı commit mesajları kullanın

## 📝 Changelog

### Son Güncellemeler

- ✅ Modüler yapı ile tam entegrasyon
- ✅ Permission-based dashboard sistemi
- ✅ Agency news modülü iyileştirmeleri
- ✅ Post yönetimi ve primary file seçimi
- ✅ Newsletter template seeder
- ✅ CI/CD workflow optimizasyonları

Detaylı değişiklik listesi için [commits](https://github.com/huseyinuslu60/livewire-alpineJS-CMS-Guncel/commits/main) sayfasını kontrol edebilirsiniz.

## 🐛 Bilinen Sorunlar

Bilinen sorunlar ve çözümleri için [Issues](https://github.com/huseyinuslu60/livewire-alpineJS-CMS-Guncel/issues) sayfasına bakın.

## 📄 Lisans

Bu proje [MIT Lisansı](LICENSE) altında lisanslanmıştır.

## 👥 Yazar

**Hüseyin Uslu**

- GitHub: [@huseyinuslu60](https://github.com/huseyinuslu60)

## 🙏 Teşekkürler

- [Laravel](https://laravel.com) - Harika PHP framework
- [Livewire](https://livewire.laravel.com) - Reactive component'ler
- [Alpine.js](https://alpinejs.dev) - Minimal JavaScript framework
- [nwidart/laravel-modules](https://github.com/nwidart/laravel-modules) - Modüler yapı
- [Spatie](https://spatie.be) - Laravel permission paketi

---

⭐ Bu projeyi beğendiyseniz yıldız vermeyi unutmayın!
---
## 🔥 Son Durum Özet (2025-11)
### Teknoloji ve Mimarî
- Laravel 12 (PHP 8.2), Livewire 3, Tailwind v4, Vite 7, Alpine.js 3
- Modüler yapı (Nwidart), Spatie Permissions
### Güvenlik İyileştirmeleri
- XSS Sanitizer (`App\Support\Sanitizer`):
  - Gallery `caption/description` sanitize; `alt_text` escape
  - Articles/News `content` sanitize
- SSRF Guard (same-origin):
  - Dış domain’den indirmeler kapalı; whitelist: `IMAGE_DOWNLOAD_ALLOWED_HOSTS`
- Güvenli Upload (`App\Traits\SecureFileUpload`):
  - MIME/extension + finfo doğrulama, kötü içerik tespiti, UUID dosya isimleri
  - Upload boyutu `.env` ile ayarlanır: `FILES_MAX_SIZE_KB`
### Performans İyileştirmeleri
- Composite indexler:
  - `files(post_id, file_path)`
  - `posts_categories(category_id, post_id)`, `posts_tags(tag_id, post_id)`
- Gallery batch update: tek sorgu + bellek içi eşleştirme
- Vite chunk-splitting: editor/vendor/module bazlı ayrım
### Editör Lazy-Load
- jQuery + Trumbowyg ana bundle’dan ayrıldı; `[data-editor]` olduğunda editor chunk yüklenir
- Dosyalar:
  - `resources/js/editor-loader.js`
  - `resources/js/editors/trumbowyg-init.js`
  - `resources/js/editors-lifecycle.js`
### Komutlar
```bash
composer format:test   # stil kontrol
composer format        # stil düzeltme
composer analyse       # statik analiz
php -d memory_limit=512M vendor/bin/phpstan analyse  # gerekirse
npm run build          # prod build
php artisan migrate:fresh --seed
```
### Ortam Değişkenleri
```env
FILES_MAX_SIZE_KB=20480
IMAGE_DOWNLOAD_ALLOWED_HOSTS=cdn.example.com,images.example.net
```
### Troubleshooting
- Editör yüklenmiyor: `[data-editor]` attribute’u yoksa editor chunk yüklenmez
- PHPStan bellek uyarısı: artırılmış memory ile çalıştırın
- Gallery preview uyuşmazlığı: `data-image-key` tutarlı olmalı (`temp:<id>` / `existing:<fileId>`)
