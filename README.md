# 📰 Livewire-AlpineJS CMS Güncel

[![CI](https://github.com/huseyinuslu60/livewire-alpineJS-CMS-Guncel/actions/workflows/ci.yml/badge.svg)](https://github.com/huseyinuslu60/livewire-alpineJS-CMS-Guncel/actions/workflows/ci.yml)

Modern Laravel 12 tabanlı, tam modüler haber ve içerik yönetim sistemi. Livewire 3 ve Alpine.js ile geliştirilmiş, kullanıcı dostu admin paneli ve güçlü özellikler sunar.

> **Not:** Bu proje aktif olarak geliştirilmektedir. Son güncellemeler için [commits](https://github.com/huseyinuslu60/livewire-alpineJS-CMS-Guncel/commits/main) sayfasını kontrol edebilirsiniz.

## 🎯 Özellikler

### 🧩 Modüler Mimari
- **Tam modüler yapı** - `nwidart/laravel-modules` ile bağımsız modül geliştirme
- **15 aktif modül** - Her modül kendi route, controller, view ve migration'larına sahip
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

### ⚡ Performans Optimizasyonları
- **Livewire Query Optimizasyonu** - Gereksiz DB sorgularını önlemek için `visibleIds` pattern kullanımı
- **Efficient Selection Management** - Selection change event'lerinde DB query'siz çalışma
- **Array-based Calculations** - `array_diff()` ile selectAll hesaplamaları
- **Automatic Selection Reset** - Filtre değişikliklerinde otomatik selection temizleme
- **Eloquent Query Optimization** - Listing sorgularında `select()` ve `with()` projection ile gereksiz kolon yüklemelerini azaltma
- **Column Projection** - İlişkilerde sadece gerekli kolonları yükleme (`with(['author:id,name'])`)
- **Optimized Services** - PostQueryService, LogService, AgencyNewsService, LastminuteService optimizasyonları

### 🧱 Geliştirme Araçları
- **CI/CD entegrasyonu** - GitHub Actions ile otomatik test ve deploy
- **Code Quality** - Laravel Pint, PHPStan ile kod kalitesi
- **Test Coverage** - Pest/PHPUnit ile kapsamlı testler (HtmlSanitizerTest, ExceptionHandlingTest)
- **Type Safety** - Livewire component'lerinde type declarations ve PHPDoc
- **Docker desteği** - Kolay geliştirme ortamı kurulumu
- **Editor Support** - Trumbowyg WYSIWYG editör entegrasyonu
- **Drag & Drop** - SortableJS ile sıralanabilir listeler
- **Model Observers** - Otomatik sanitization ve business logic için observer pattern
- **Service Layer** - Business logic'in servis katmanında merkezi yönetimi

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
# veya (memory limit artırılmış)
php -d memory_limit=512M vendor/bin/phpstan analyse
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
| Kod stili | Laravel Pint | `composer format` veya `composer format:test` |
| Statik analiz | PHPStan (Level 5) | `composer analyse` (512M memory limit ile) |
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
- **XSS Protection** - Kapsamlı HTML sanitization ve output escaping
  - **HTML Content Sanitization** - Post, Article, AgencyNews içeriklerinde whitelist tabanlı HTML temizleme
  - **File Meta Protection** - Dosya adları (`getClientOriginalName()`), alt_text ve caption alanlarında XSS koruması
  - **Model Observers** - Otomatik sanitization için PostObserver, ArticleObserver, AgencyNewsObserver, FileObserver
  - **Service Layer Sanitization** - PostCreationService, PostUpdateService, ArticleService, FileService katmanında ek koruma
  - **Gallery Meta Protection** - Gallery JSON içindeki filename, description, alt_text sanitization
- **SQL Injection Protection** - Eloquent ORM kullanımı ile parametreli sorgular
- **File Upload Security** - Güvenli dosya yükleme kontrolleri ve validasyon
  - **MIME Type Validation** - Çift katmanlı MIME type kontrolü (getMimeType + finfo)
  - **Content Scanning** - Dosya içeriğinde zararlı pattern taraması
  - **Extension Whitelist** - Sadece izin verilen dosya uzantıları
  - **UUID Filenames** - Güvenli dosya adlandırma (UUID tabanlı)
- **Role-based Access Control** - Rol tabanlı erişim kontrolü
- **Policy-based Authorization** - Model bazlı yetkilendirme
- **Secure File Storage** - Private ve public dosya yönetimi
- **Defense in Depth** - Observer, Service ve Livewire katmanlarında çoklu koruma

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

### Son Güncellemeler (2025)

#### 🔒 Güvenlik İyileştirmeleri
- ✅ **XSS Koruması - HTML Sanitization** - Post, Article, AgencyNews içeriklerinde whitelist tabanlı HTML temizleme
- ✅ **XSS Koruması - Dosya Meta** - `getClientOriginalName()`, `alt_text`, `caption` alanlarında XSS koruması
- ✅ **Model Observers** - PostObserver, ArticleObserver, AgencyNewsObserver, FileObserver, PostFileObserver eklendi
- ✅ **Service Layer Sanitization** - PostCreationService, PostUpdateService, ArticleService, FileService katmanında sanitization
- ✅ **Gallery Meta Protection** - Gallery JSON içindeki filename, description, alt_text sanitization
- ✅ **Sanitizer Service** - `app/Support/Sanitizer.php` ile merkezi HTML sanitization servisi
- ✅ **Test Coverage** - HtmlSanitizerTest ile XSS saldırı senaryoları test edildi

#### ⚡ Performans İyileştirmeleri
- ✅ **Query Optimization - PostQueryService** - Listing sorgularında sadece gerekli kolonlar yükleniyor
- ✅ **Query Optimization - LogService** - Log listing'de column projection uygulandı
- ✅ **Query Optimization - AgencyNewsService** - Gereksiz kolon yüklemeleri kaldırıldı
- ✅ **Query Optimization - LastminuteService** - Optimize edilmiş select() ve with() kullanımı
- ✅ **Relation Projection** - `with(['author:id,name', 'primaryFile:id,file_path'])` ile sadece gerekli ilişki kolonları
- ✅ **Export Optimization** - LogIndex exportLogs() metodunda da optimizasyon uygulandı

#### 🐛 Bug Fix'ler
- ✅ **Livewire Property Fixes** - FileIndex (`mimeType`), StockIndex (`status`), LogIndex (`action`, `user_id`, `date_from`, `date_to`) property'leri eklendi
- ✅ **HasBulkActions Compatibility** - `applyBulkAction(): void` return type uyumluluğu sağlandı
- ✅ **HasSearchAndFilters Integration** - Tüm listing component'lerinde trait entegrasyonu tamamlandı

#### 🏗️ Mimari İyileştirmeleri
- ✅ **Yeni Servisler** - PostCreationService, PostUpdateService, PostQueryService, PostMediaService, PostBulkActionService
- ✅ **Trait'ler** - HasBulkActions, HasSearchAndFilters, HasColumnPreferences trait'leri eklendi
- ✅ **Exception Handling** - HandlesExceptionsWithToast trait'i ile merkezi hata yönetimi
- ✅ **Post Enums** - PostType, PostStatus, PostPosition enum'ları eklendi

#### 🔧 Kod Kalitesi İyileştirmeleri
- ✅ **Laravel Pint** - 361 dosyada 59 stil sorunu düzeltildi, tüm kod Laravel standartlarına uygun hale getirildi
- ✅ **PHPStan Level 5** - Statik analiz ile kritik hatalar tespit edildi ve düzeltildi
- ✅ **Type Safety** - Property tanımlamaları ve type hint'ler eklendi
- ✅ **Dependency Injection** - `new PostsService()` yerine constructor injection kullanımı
- ✅ **Code Quality** - Undefined property, null coalesce ve instanceof hataları düzeltildi

#### 📊 Önceki Güncellemeler
- ✅ **Livewire Performans Optimizasyonları** - LogIndex, PostIndex, ArticleIndex component'lerinde gereksiz DB sorguları kaldırıldı
- ✅ **visibleIds Pattern** - Selection yönetimi için DB query'siz çalışma
- ✅ **Array-based Calculations** - `array_diff()` ile selectAll hesaplamaları
- ✅ **Automatic Selection Reset** - Filtre değişikliklerinde otomatik selection temizleme
- ✅ Modüler yapı ile tam entegrasyon
- ✅ Permission-based dashboard sistemi
- ✅ Agency news modülü iyileştirmeleri
- ✅ Post yönetimi ve primary file seçimi
- ✅ Newsletter template seeder
- ✅ CI/CD workflow optimizasyonları
- ✅ Boş migration dosyaları temizlendi

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
