# 📐 Proje Yapısı ve Mimari Dokümantasyonu

Bu dokümantasyon, projenin tüm yapısını, bileşenlerin nerede kullanıldığını ve birbirleriyle nasıl etkileşim kurduğunu detaylı olarak açıklar.

---

## 📁 Genel Proje Yapısı

```
bg-laravel/
├── app/                    # Ana uygulama kodu
│   ├── Console/           # Artisan komutları
│   ├── Contracts/         # Interface'ler
│   ├── Helpers/           # Yardımcı fonksiyonlar
│   ├── Http/              # HTTP katmanı (Controllers, Middleware)
│   ├── Livewire/          # Livewire concerns (trait'ler)
│   ├── Models/            # Ana modeller
│   ├── Observers/         # Model observer'ları
│   ├── Policies/          # Yetkilendirme policy'leri
│   ├── Providers/         # Service provider'lar
│   ├── Services/           # Genel servisler
│   ├── Support/           # Destek sınıfları
│   └── Traits/            # Genel trait'ler
├── Modules/               # Modüler yapı (15 modül)
├── config/                # Konfigürasyon dosyaları
├── database/              # Migration'lar ve seed'ler
├── resources/            # View'lar, CSS, JS
├── routes/               # Route tanımları
├── tests/                # Test dosyaları
└── public/               # Public dosyalar
```

---

## 🏗️ Mimari Katmanlar

### 1. **Modüler Mimari (Modules/)**

Proje `nwidart/laravel-modules` paketi ile tam modüler yapıya sahiptir. Her modül bağımsız bir uygulama gibi çalışır.

#### Modül Yapısı

Her modül aşağıdaki standart yapıya sahiptir:

```
Modules/ModuleName/
├── app/
│   ├── Http/
│   │   ├── Controllers/      # Modül controller'ları
│   │   └── Livewire/          # Modül Livewire component'leri
│   ├── Models/               # Modül modelleri
│   ├── Services/             # Modül servisleri (business logic)
│   ├── Enums/                # Modül enum'ları
│   ├── Observers/            # Modül observer'ları (opsiyonel)
│   └── Providers/            # Modül service provider'ları
├── config/                   # Modül konfigürasyonları
├── database/
│   ├── migrations/           # Modül migration'ları
│   └── seeders/              # Modül seed'leri
├── resources/
│   ├── views/                # Modül Blade view'ları
│   ├── js/                   # Modül JavaScript dosyaları
│   └── scss/                 # Modül SCSS dosyaları
├── routes/
│   ├── web.php               # Modül web route'ları
│   └── api.php               # Modül API route'ları (opsiyonel)
└── tests/                    # Modül test dosyaları
```

#### Aktif Modüller ve Kullanımları

| Modül | Açıklama | Ana Kullanım Alanları |
|-------|----------|----------------------|
| **Posts** | Haber ve içerik yönetimi | Post oluşturma, düzenleme, yayınlama, galeri yönetimi |
| **Articles** | Makale yönetimi | Makale oluşturma, düzenleme, yayınlama |
| **AgencyNews** | Ajans haberleri | Dış kaynak haber entegrasyonu ve yönetimi |
| **Authors** | Yazar yönetimi | Yazar profilleri, biyografiler, fotoğraflar |
| **Categories** | Kategori yönetimi | Hiyerarşik kategori yapısı, makale kategorileri |
| **Comments** | Yorum sistemi | İçerik yorumları, moderasyon, onay süreci |
| **Files** | Dosya yönetimi | Dosya yükleme, görüntüleme, metadata yönetimi |
| **Headline** | Manşet yönetimi | Ana sayfa manşetleri, sürmanşetler, drag-drop sıralama |
| **Lastminutes** | Son dakika haberleri | Acil haber yönetimi, otomatik süre dolumu |
| **Logs** | Sistem logları | Kullanıcı aktiviteleri, sistem olayları, audit trail |
| **Newsletters** | Bülten sistemi | E-posta bülteni yönetimi, abonelik, template'ler |
| **Banks** | Banka yönetimi | Banka bilgileri, hisse senedi yönetimi, yatırımcı soruları |
| **Roles** | Rol yönetimi | Kullanıcı rolleri, izin yönetimi |
| **Settings** | Sistem ayarları | Site ayarları, menü yönetimi |
| **User** | Kullanıcı yönetimi | Kullanıcı CRUD, rol atama, profil yönetimi |

---

## 🎯 Ana Uygulama Yapısı (app/)

### 1. **Contracts/ (Interface'ler)**

Type-safe kod için interface'ler.

#### `SupportsSelectionReset`
- **Kullanım:** Bulk action'ları olan component'lerde selection reset için
- **Implement Edenler:** `PostIndex`, `FileIndex`, `LogIndex`, `StockIndex`
- **Metod:** `resetSelection(): void`

#### `SupportsToastErrors`
- **Kullanım:** Toast notification gönderebilen component'ler için
- **Implement Edenler:** 40+ Livewire component (tüm `InteractsWithToast` kullananlar)
- **Metod:** `toastError(string $message): void`

---

### 2. **Livewire/Concerns/ (Trait'ler)**

Livewire component'lerinde ortak işlevsellik için trait'ler.

#### `HasBulkActions`
- **Kullanım:** Toplu işlem yapabilen listing component'lerinde
- **Kullananlar:** `PostIndex`, `FileIndex`, `LogIndex`, `StockIndex`, `InvestorQuestionIndex`
- **Özellikler:**
  - `$selectAll`: Tümünü seç checkbox durumu
  - `$bulkAction`: Uygulanacak bulk action
  - `resetSelection()`: Seçimi temizle
  - `updatedSelectAll()`: Tümünü seç değiştiğinde
  - `applyBulkAction()`: Abstract metod - component'te implement edilmeli
- **Gereksinimler:**
  - `getSelectedItemsPropertyName()`: Seçili item property adı (örn: 'selectedPosts')
  - `getVisibleItemIds()`: Görünen item ID'leri (abstract)

#### `HasSearchAndFilters`
- **Kullanım:** Arama ve filtreleme özelliği olan listing component'lerinde
- **Kullananlar:** `PostIndex`, `FileIndex`, `LogIndex`, `StockIndex`, `LastminuteIndex`, `AgencyNewsIndex`
- **Özellikler:**
  - `$search`: Arama sorgusu
  - `getFilterProperties()`: Filtre property'leri (override edilebilir)
  - `resetFilters()`: Filtreleri sıfırla
  - `updatedSearch()`: Arama değiştiğinde otomatik tetiklenir
  - `onFilterUpdated()`: Filtre güncellendiğinde (selection reset + page reset)
- **Entegrasyon:** `HasBulkActions` ile birlikte kullanıldığında otomatik selection reset

#### `HasColumnPreferences`
- **Kullanım:** Kullanıcı kolon görünürlük tercihlerini yöneten component'lerde
- **Kullananlar:** `PostIndex`, `StockIndex`
- **Özellikler:**
  - `$visibleColumns`: Görünür kolonlar (array<string, bool>)
  - `loadUserColumnPreferences()`: Kullanıcı tercihlerini yükle
  - `updatedVisibleColumns()`: Tercih değiştiğinde otomatik kaydet
- **Gereksinimler:**
  - `getDefaultColumns()`: Varsayılan kolon görünürlüğü (abstract)

#### `InteractsWithToast`
- **Kullanım:** Toast notification gönderebilen tüm component'lerde
- **Kullananlar:** 40+ Livewire component
- **Özellikler:**
  - `toast($type, $message, $extra, $persistOnRedirect)`: Genel toast metodu
  - `toastSuccess()`: Başarı mesajı
  - `toastError()`: Hata mesajı (public - interface uyumu için)
  - `toastInfo()`: Bilgi mesajı
  - `toastWarning()`: Uyarı mesajı
- **Not:** `toastError()` public yapıldı çünkü `SupportsToastErrors` interface'i gerektiriyor

#### `InteractsWithModal`
- **Kullanım:** Modal açma/kapama işlevselliği olan component'lerde
- **Kullananlar:** `PostIndex`, `AgencyNewsIndex`, `CategoryIndex`, `NewsletterIndex`, `RoleManagement`
- **Özellikler:**
  - Modal state yönetimi
  - Modal açma/kapama metodları

---

### 3. **Traits/ (Genel Trait'ler)**

Uygulama genelinde kullanılan trait'ler.

#### `HandlesExceptionsWithToast`
- **Kullanım:** Hata yönetimi yapan tüm component'lerde
- **Kullananlar:** 40+ Livewire component
- **Özellikler:**
  - `handleException($e, $userMessage, $context)`: Exception'ı logla ve kullanıcıya göster
  - Detaylı log context (user_id, component, exception details)
  - Laravel exception handler'a report etme (Sentry, Bugsnag için)
  - Toast notification gönderme (`SupportsToastErrors` interface kontrolü ile)
- **Entegrasyon:** `InteractsWithToast` trait'i ile birlikte kullanılır

#### `ValidationMessages`
- **Kullanım:** Türkçe validation mesajları gösteren component'lerde
- **Kullananlar:** Form component'leri (Create, Edit)
- **Özellikler:**
  - Türkçe validation mesajları
  - Custom validation rules

#### `AuditFields`
- **Kullanım:** Created/Updated bilgilerini takip eden modellerde
- **Kullananlar:** Model'ler
- **Özellikler:**
  - `created_by`, `updated_by` alanları
  - Otomatik user tracking

#### `HandlesRequestContext`
- **Kullanım:** Request context bilgilerini saklayan sınıflarda
- **Özellikler:**
  - IP adresi, user agent tracking

---

### 4. **Support/ (Destek Sınıfları)**

Yardımcı ve utility sınıfları.

#### `Sanitizer`
- **Kullanım:** HTML içerik sanitization için
- **Kullananlar:** 
  - Model Observer'lar: `PostObserver`, `ArticleObserver`, `AgencyNewsObserver`
  - Service'ler: `PostCreationService`, `PostUpdateService`, `ArticleService`
  - Livewire Component'ler: `FileUpload`, `PostEdit`, `PostCreateGallery`
- **Metodlar:**
  - `sanitizeHtml($html)`: HTML içeriği temizle (whitelist tabanlı)
  - `escape($value)`: Plain text escape (htmlspecialchars)
- **Özellikler:**
  - Whitelist tabanlı tag ve attribute kontrolü
  - Tehlikeli protokol temizleme (javascript:, data:, vb.)
  - Event handler temizleme (onclick, onerror, vb.)
  - Style attribute temizleme
  - Boş tag temizleme

#### `Pagination`
- **Kullanım:** Pagination limit kontrolü için
- **Kullananlar:** Listing component'leri
- **Metod:** `clamp($perPage)`: Per page değerini güvenli aralığa sınırla

---

### 5. **Services/ (Genel Servisler)**

Uygulama genelinde kullanılan servisler.

#### `FileUploadService`
- **Kullanım:** Güvenli dosya yükleme için
- **Kullananlar:** `PostCreateGallery`, `PostEdit`, `FileUpload`
- **Özellikler:**
  - MIME type validation (çift katmanlı)
  - Content scanning (zararlı pattern taraması)
  - Extension whitelist kontrolü
  - UUID tabanlı dosya adlandırma
  - Image optimization

#### `ContentSuggestionService`
- **Kullanım:** İçerik önerileri için
- **Kullananlar:** Dashboard, içerik öneri sistemi
- **Özellikler:**
  - Cache'lenmiş içerik önerileri
  - 30 dakikalık cache blokları

---

### 6. **Helpers/ (Yardımcı Fonksiyonlar)**

Global helper fonksiyonlar ve sınıflar.

#### `SystemHelper`
- **Kullanım:** Sistem geneli yardımcı fonksiyonlar
- **Fonksiyonlar:**
  - `uploadImage()`: Resim yükleme (deprecated - FileUploadService kullanılmalı)
  - `turkishDate()`: Türkçe tarih formatı
  - `formatBytes()`: Byte formatı
  - `slugify()`: URL-friendly slug oluşturma
- **Not:** `composer.json`'da `files` autoload ile global olarak yüklenir

#### `PermissionHelper`
- **Kullanım:** Yetkilendirme kontrolleri için
- **Kullananlar:** Blade view'lar, controller'lar
- **Metodlar:**
  - `can($permission)`: Tek izin kontrolü
  - `canAny($permissions)`: Herhangi bir izin kontrolü
  - `canAll($permissions)`: Tüm izinler kontrolü
  - `registerBladeDirectives()`: Blade directive'leri kaydet (@can, @cannot, vb.)

#### `MenuHelper`
- **Kullanım:** Admin menü yapısını oluşturmak için
- **Kullananlar:** Admin layout, sidebar
- **Özellikler:**
  - Kullanıcı bazlı menü filtreleme (permission-based)
  - 10 dakikalık cache
  - Rol bazlı cache key'leri
  - Hiyerarşik menü yapısı

---

### 7. **Observers/ (Model Observer'ları)**

Model lifecycle event'lerini yakalayan observer'lar.

#### `PostObserver`
- **Model:** `Modules\Posts\Models\Post`
- **Event'ler:** `creating`, `updating`
- **İşlevler:**
  - Content sanitization (XSS koruması)
  - Post position sync (featured items ile)
- **Kayıt:** `AppServiceProvider::boot()`

#### `ArticleObserver`
- **Model:** `Modules\Articles\Models\Article`
- **Event'ler:** `creating`, `updating`
- **İşlevler:**
  - Article text sanitization (XSS koruması)
- **Kayıt:** `AppServiceProvider::boot()`

#### `AgencyNewsObserver`
- **Model:** `Modules\AgencyNews\Models\AgencyNews`
- **Event'ler:** `creating`, `updating`
- **İşlevler:**
  - Content sanitization (XSS koruması)
- **Kayıt:** `AppServiceProvider::boot()`

#### `FileObserver`
- **Model:** `Modules\Files\Models\File`
- **Event'ler:** `creating`, `updating`
- **İşlevler:**
  - Title, alt_text, caption escape (XSS koruması)
- **Kayıt:** `AppServiceProvider::boot()`

#### `PostFileObserver`
- **Model:** `Modules\Posts\Models\File`
- **Event'ler:** `creating`, `updating`
- **İşlevler:**
  - Title, alt_text, caption escape (XSS koruması)
- **Kayıt:** `AppServiceProvider::boot()`

---

### 8. **Models/ (Ana Modeller)**

Uygulama genelinde kullanılan modeller.

#### `User`
- **Kullanım:** Kullanıcı yönetimi, authentication
- **Özellikler:**
  - Spatie Permission entegrasyonu
  - `table_columns` JSON field (kullanıcı kolon tercihleri)
  - Audit fields (created_by, updated_by)

#### `Module`
- **Kullanım:** Modül yönetimi, aktif/pasif kontrolü
- **Özellikler:**
  - `is_active`: Modül aktif/pasif durumu
  - `permissions`: Modül izinleri (JSON)
  - `sort_order`: Menü sıralaması

#### `MenuItem`
- **Kullanım:** Admin menü yapısı
- **Özellikler:**
  - Hiyerarşik menü yapısı
  - Permission-based görünürlük

---

### 9. **Http/Middleware/ (Middleware'ler)**

HTTP isteklerini filtreleyen middleware'ler.

#### `CheckPermission`
- **Kullanım:** Route'larda permission kontrolü
- **Kullanım:** `Route::middleware(['permission:view posts'])`

#### `PermissionMiddleware`
- **Kullanım:** Permission bazlı erişim kontrolü
- **Alternatif:** `permission:` middleware alias'ı

#### `RoleMiddleware`
- **Kullanım:** Rol bazlı erişim kontrolü
- **Kullanım:** `Route::middleware(['role:admin'])`

#### `RoleOrPermissionMiddleware`
- **Kullanım:** Rol veya permission bazlı erişim kontrolü
- **Kullanım:** `Route::middleware(['role_or_permission:admin|view posts'])`

#### `ModuleActiveMiddleware`
- **Kullanım:** Modül aktif/pasif kontrolü
- **Kullanım:** Modül route'larında otomatik

#### `LogHttpRequests`
- **Kullanım:** HTTP request'lerini loglama
- **Özellikler:** Request/response logging

#### `SecurityHeaders`
- **Kullanım:** Güvenlik header'ları ekleme
- **Özellikler:** X-Frame-Options, X-Content-Type-Options, vb.

---

### 10. **Http/Controllers/ (Controller'lar)**

HTTP isteklerini yöneten controller'lar.

#### `DashboardController`
- **Route:** `/dashboard`
- **Kullanım:** Ana dashboard sayfası
- **Özellikler:** Permission-based dashboard içeriği

#### `AuthController`
- **Route:** `/login`, `/logout`
- **Kullanım:** Authentication işlemleri
- **Özellikler:** Rate limiting (5 attempt/minute)

#### `ModuleController`
- **Route:** `/modules`
- **Kullanım:** Modül yönetimi (sadece super_admin)
- **Özellikler:** Modül aktif/pasif yapma, güncelleme

---

### 11. **Providers/ (Service Provider'lar)**

Uygulama servislerini kaydeden provider'lar.

#### `AppServiceProvider`
- **Kullanım:** Uygulama geneli servis kayıtları
- **İşlevler:**
  - Morph map kayıtları (polymorphic relationships)
  - Permission helper Blade directive'leri
  - Model observer kayıtları (Post, Article, AgencyNews, File)

#### `LivewireServiceProvider`
- **Kullanım:** Livewire konfigürasyonu
- **İşlevler:** Livewire component discovery

#### `EventServiceProvider`
- **Kullanım:** Event listener kayıtları
- **İşlevler:** Authentication event listener'ları

---

## 📦 Modül Detayları

### Posts Modülü

En kapsamlı modül. Haber, galeri ve video içerik yönetimi.

#### Yapı:
```
Modules/Posts/
├── app/
│   ├── Enums/
│   │   ├── PostType.php          # news, gallery, video
│   │   ├── PostStatus.php        # draft, published, archived
│   │   └── PostPosition.php      # normal, featured, breaking
│   ├── Livewire/
│   │   ├── PostIndex.php         # Listing (HasBulkActions, HasSearchAndFilters)
│   │   ├── PostCreateNews.php    # Haber oluşturma
│   │   ├── PostCreateGallery.php  # Galeri oluşturma
│   │   ├── PostCreateVideo.php   # Video oluşturma
│   │   ├── PostEdit.php          # Ana edit component (orchestrator)
│   │   ├── PostEditContentForm.php    # İçerik formu
│   │   ├── PostEditMetaForm.php       # Meta bilgiler formu
│   │   ├── PostEditMetaFormSidebar.php # Sidebar meta formu
│   │   ├── PostEditRelationsForm.php  # Kategori/tag ilişkileri
│   │   └── PostEditMediaManager.php   # Medya yönetimi
│   ├── Models/
│   │   ├── Post.php              # Ana post modeli
│   │   ├── File.php              # Post dosyaları modeli
│   │   └── Tag.php               # Tag modeli
│   └── Services/
│       ├── PostsService.php           # Orchestrator service
│       ├── PostCreationService.php    # Post oluşturma logic
│       ├── PostUpdateService.php      # Post güncelleme logic
│       ├── PostQueryService.php       # Query optimizasyonu
│       ├── PostMediaService.php       # Medya yönetimi
│       └── PostBulkActionService.php  # Toplu işlemler
```

#### Servis Kullanımı:

**PostsService (Orchestrator):**
- **Kullanım:** Livewire component'lerde facade gibi kullanılır
- **Delegasyon:** Alt servislere işlemleri yönlendirir
- **Kullananlar:** `PostIndex`, `PostCreateNews`, `PostCreateGallery`, `PostCreateVideo`, `PostEdit`

**PostCreationService:**
- **İşlev:** Yeni post oluşturma
- **Özellikler:**
  - Content sanitization
  - File handling
  - Category/tag ilişkileri
  - Gallery data preparation

**PostUpdateService:**
- **İşlev:** Mevcut post güncelleme
- **Özellikler:**
  - Content sanitization
  - File updates
  - Category/tag sync

**PostQueryService:**
- **İşlev:** Optimize edilmiş listing sorguları
- **Özellikler:**
  - Column projection (`select()`)
  - Relation projection (`with(['author:id,name'])`)
  - Filter kombinasyonları
  - Pagination

**PostMediaService:**
- **İşlev:** Post medya yönetimi
- **Özellikler:**
  - File upload handling
  - Gallery data management
  - Primary file selection
  - File metadata sanitization

**PostBulkActionService:**
- **İşlev:** Toplu işlemler (delete, publish, archive)
- **Kullananlar:** `PostIndex::applyBulkAction()`

---

### Files Modülü

Dosya yükleme ve yönetim sistemi.

#### Yapı:
```
Modules/Files/
├── app/
│   ├── Livewire/
│   │   ├── FileIndex.php      # Dosya listesi (HasBulkActions, HasSearchAndFilters)
│   │   └── FileUpload.php      # Dosya yükleme component'i
│   ├── Models/
│   │   └── File.php           # Dosya modeli
│   └── Services/
│       └── FileService.php    # Dosya business logic
```

#### Kullanım:
- **FileIndex:** Admin panelinde dosya listesi, arama, filtreleme, toplu işlemler
- **FileUpload:** Dosya yükleme modal'ı, metadata girişi
- **FileService:** Dosya CRUD işlemleri, metadata yönetimi

---

### Logs Modülü

Sistem aktivite logları.

#### Yapı:
```
Modules/Logs/
├── app/
│   ├── Livewire/
│   │   ├── LogIndex.php       # Log listesi (HasBulkActions, HasSearchAndFilters)
│   │   └── LogDetail.php      # Log detay sayfası
│   ├── Models/
│   │   └── UserLog.php        # Log modeli
│   └── Services/
│       └── LogService.php     # Log query optimizasyonu
```

#### Kullanım:
- **LogIndex:** Sistem loglarını listeleme, filtreleme, export
- **LogDetail:** Tek bir log kaydının detayları
- **LogService:** Optimize edilmiş log sorguları (column projection)

---

### Categories Modülü

Hiyerarşik kategori yönetimi.

#### Yapı:
```
Modules/Categories/
├── app/
│   ├── Livewire/
│   │   ├── CategoryIndex.php  # Kategori listesi
│   │   ├── CategoryCreate.php # Kategori oluşturma
│   │   └── CategoryEdit.php   # Kategori düzenleme
│   ├── Models/
│   │   └── Category.php       # Kategori modeli (self-referencing)
│   └── Services/
│       └── CategoryService.php # Kategori business logic
```

#### Özellikler:
- Hiyerarşik kategori yapısı (parent_id)
- Slug otomatik oluşturma
- Meta bilgiler (title, description)

---

### Articles Modülü

Makale yönetimi.

#### Yapı:
```
Modules/Articles/
├── app/
│   ├── Livewire/
│   │   ├── ArticleIndex.php   # Makale listesi
│   │   ├── ArticleCreate.php  # Makale oluşturma
│   │   └── ArticleEdit.php    # Makale düzenleme
│   ├── Models/
│   │   └── Article.php        # Makale modeli
│   └── Services/
│       └── ArticleService.php # Makale business logic
```

#### Özellikler:
- HTML içerik yönetimi (sanitized)
- Yazar atama
- Kategori ilişkileri
- Yayın durumu yönetimi

---

### AgencyNews Modülü

Ajans haberleri entegrasyonu.

#### Yapı:
```
Modules/AgencyNews/
├── app/
│   ├── Livewire/
│   │   └── AgencyNewsIndex.php # Ajans haberleri listesi
│   ├── Models/
│   │   └── AgencyNews.php      # Ajans haberi modeli
│   └── Services/
│       └── AgencyNewsService.php # Ajans haber query optimizasyonu
```

#### Özellikler:
- Dış kaynak haber entegrasyonu
- Kategori bazlı filtreleme
- Görsel kontrolü (has_image)

---

### Lastminutes Modülü

Son dakika haberleri.

#### Yapı:
```
Modules/Lastminutes/
├── app/
│   ├── Livewire/
│   │   ├── LastminuteIndex.php  # Son dakika listesi
│   │   ├── LastminuteCreate.php # Son dakika oluşturma
│   │   └── LastminuteEdit.php   # Son dakika düzenleme
│   ├── Models/
│   │   └── Lastminute.php       # Son dakika modeli
│   └── Services/
│       └── LastminuteService.php # Son dakika business logic
```

#### Özellikler:
- Süre yönetimi (end_at)
- Otomatik süre dolumu
- Redirect URL yönetimi
- Weight bazlı sıralama

---

### Newsletters Modülü

E-posta bülteni sistemi.

#### Yapı:
```
Modules/Newsletters/
├── app/
│   ├── Livewire/
│   │   ├── NewsletterIndex.php      # Bülten listesi
│   │   ├── NewsletterCreate.php     # Bülten oluşturma
│   │   ├── NewsletterEdit.php      # Bülten düzenleme
│   │   ├── NewsletterUserIndex.php # Abone listesi
│   │   ├── NewsletterLogIndex.php   # Gönderim logları
│   │   ├── TemplateIndex.php        # Template listesi
│   │   ├── TemplateCreate.php       # Template oluşturma
│   │   └── TemplateEdit.php         # Template düzenleme
│   ├── Models/
│   │   ├── Newsletter.php           # Bülten modeli
│   │   ├── NewsletterUser.php      # Abone modeli
│   │   ├── NewsletterLog.php       # Gönderim log modeli
│   │   └── NewsletterTemplate.php  # Template modeli
│   └── Services/
│       └── NewsletterService.php    # Bülten business logic
```

#### Özellikler:
- HTML template yönetimi
- Abone yönetimi
- Gönderim logları
- Template sistemi

---

### Banks Modülü

Banka ve hisse senedi yönetimi.

#### Yapı:
```
Modules/Banks/
├── app/
│   ├── Livewire/
│   │   ├── StockIndex.php            # Hisse listesi (HasBulkActions, HasSearchAndFilters)
│   │   ├── StockCreate.php           # Hisse oluşturma
│   │   ├── StockEdit.php             # Hisse düzenleme
│   │   ├── InvestorQuestionIndex.php # Yatırımcı soruları listesi
│   │   └── InvestorQuestionAnswer.php # Soru cevaplama
│   ├── Models/
│   │   ├── Stock.php                 # Hisse modeli
│   │   └── InvestorQuestion.php      # Yatırımcı sorusu modeli
│   └── Services/
│       └── StockService.php          # Hisse business logic
```

---

### Comments Modülü

Yorum sistemi.

#### Yapı:
```
Modules/Comments/
├── app/
│   ├── Livewire/
│   │   └── CommentsIndex.php    # Yorum listesi ve moderasyon
│   ├── Models/
│   │   └── Comment.php        # Yorum modeli
│   └── Services/
│       └── CommentService.php # Yorum business logic
```

#### Özellikler:
- Yorum onaylama/reddetme
- Yorum düzenleme
- İçerik bazlı filtreleme

---

### Headline Modülü

Manşet ve sürmanşet yönetimi.

#### Yapı:
```
Modules/Headline/
├── app/
│   ├── Http/Livewire/
│   │   └── Manage.php         # Manşet yönetimi (drag-drop)
│   └── Services/
│       └── FeaturedService.php # Featured items yönetimi
```

#### Özellikler:
- Drag-drop sıralama (SortableJS)
- Post position sync
- Featured items yönetimi

---

### Roles Modülü

Rol ve yetki yönetimi.

#### Yapı:
```
Modules/Roles/
├── app/
│   ├── Livewire/
│   │   └── RoleManagement.php # Rol CRUD ve izin yönetimi
│   └── Services/
│       └── RoleService.php    # Rol business logic
```

#### Özellikler:
- Spatie Permission entegrasyonu
- Rol oluşturma/düzenleme
- İzin atama

---

### Settings Modülü

Sistem ayarları.

#### Yapı:
```
Modules/Settings/
├── app/
│   ├── Livewire/
│   │   ├── SiteSettings.php        # Site ayarları
│   │   └── Http/Livewire/
│   │       └── MenuManagement.php  # Menü yönetimi
│   └── Services/
│       └── SettingsService.php     # Ayar yönetimi
```

#### Özellikler:
- Site geneli ayarlar
- Menü yapısı yönetimi
- Tab bazlı ayar grupları

---

### User Modülü

Kullanıcı yönetimi.

#### Yapı:
```
Modules/User/
├── app/
│   ├── Livewire/
│   │   ├── UserIndex.php   # Kullanıcı listesi
│   │   ├── UserCreate.php # Kullanıcı oluşturma
│   │   └── UserEdit.php   # Kullanıcı düzenleme
│   └── Services/
│       └── UserService.php # Kullanıcı business logic
```

#### Özellikler:
- Kullanıcı CRUD
- Rol atama
- Profil yönetimi

---

### Authors Modülü

Yazar yönetimi.

#### Yapı:
```
Modules/Authors/
├── app/
│   ├── Livewire/
│   │   ├── AuthorIndex.php  # Yazar listesi
│   │   ├── AuthorCreate.php # Yazar oluşturma
│   │   └── AuthorEdit.php   # Yazar düzenleme
│   ├── Models/
│   │   └── Author.php       # Yazar modeli
│   └── Services/
│       └── AuthorService.php # Yazar business logic
```

#### Özellikler:
- Yazar profilleri
- Biyografi yönetimi
- Fotoğraf yükleme
- Ana sayfa görünürlüğü

---

## 🔄 Veri Akışı ve Etkileşimler

### 1. **Post Oluşturma Akışı**

```
User Input (PostCreateNews)
    ↓
PostsService::create()
    ↓
PostCreationService::create()
    ├── Content Sanitization (Sanitizer::sanitizeHtml)
    ├── File Upload (FileUploadService)
    ├── Post Model Create
    ↓
PostObserver::creating()
    ├── Content Sanitization (tekrar - defense in depth)
    └── Post Position Sync
    ↓
Database (Post kaydedilir)
    ↓
Category/Tag Relations (sync)
    ↓
File Relations (PostMediaService)
    ↓
Response (Toast notification)
```

### 2. **Listing Component Akışı**

```
User Request (PostIndex)
    ↓
PostIndex::getPosts()
    ↓
PostQueryService::getFilteredQuery()
    ├── Column Projection (select([...]))
    ├── Relation Projection (with(['author:id,name']))
    ├── Filter Application
    └── Pagination
    ↓
View Render
    ├── HasBulkActions (selection management)
    ├── HasSearchAndFilters (filter UI)
    └── HasColumnPreferences (column visibility)
```

### 3. **Bulk Action Akışı**

```
User Selects Items (PostIndex)
    ↓
HasBulkActions::updatedSelectAll()
    ├── visibleStockIds array'den ID'ler alınır (DB query YOK)
    └── selectedPosts array'i güncellenir
    ↓
User Clicks Bulk Action
    ↓
PostIndex::applyBulkAction()
    ↓
PostBulkActionService::handle()
    ├── Permission Check
    ├── Action Execution (delete/publish/archive)
    └── Selection Reset
    ↓
Response (Toast notification)
```

### 4. **Filter Değişikliği Akışı**

```
User Changes Filter (PostIndex)
    ↓
HasSearchAndFilters::updated($propertyName)
    ↓
HasSearchAndFilters::onFilterUpdated()
    ├── SupportsSelectionReset kontrolü
    ├── resetSelection() çağrılır (HasBulkActions)
    └── resetPage() çağrılır (WithPagination)
    ↓
Component Re-render
    ↓
getPosts() yeniden çağrılır (yeni filtrelerle)
```

### 5. **Exception Handling Akışı**

```
Exception Thrown (Herhangi bir component'te)
    ↓
HandlesExceptionsWithToast::handleException()
    ├── Log::error() (detaylı context ile)
    ├── report() (Laravel exception handler)
    └── Toast Notification
        ├── SupportsToastErrors kontrolü
        ├── toastError() çağrılır (InteractsWithToast)
        └── dispatch('toast', ...) (Livewire event)
    ↓
Frontend (Alpine.js toast gösterir)
```

---

## 🎨 Frontend Yapısı

### Blade View'lar

#### Layout Yapısı
```
resources/views/
├── layouts/
│   ├── app.blade.php        # Ana layout
│   ├── admin.blade.php      # Admin panel layout
│   └── components/          # Blade component'leri
├── components/              # Reusable component'ler
└── modules/                 # Modül view'ları (her modül kendi view'larına sahip)
```

#### Livewire Component View'ları
- Her Livewire component kendi view'ına sahip
- Modül view'ları: `Modules/ModuleName/resources/views/livewire/`
- Component view'ları: `livewire.component-name.blade.php`

### JavaScript Yapısı

#### Ana Dosyalar
```
resources/js/
├── app.js                   # Ana JavaScript entry point
├── bootstrap.js             # Alpine.js, Livewire setup
├── components/              # Alpine.js component'leri
└── modules/                # Modül JavaScript dosyaları
```

#### Alpine.js Kullanımı
- Toast notifications
- Modal yönetimi
- Form validasyonu
- UI interaksiyonları

### CSS Yapısı

#### Tailwind CSS 4
- Utility-first CSS framework
- Modül bazlı SCSS dosyaları
- Vite ile build edilir

---

## 🔐 Güvenlik Katmanları

### 1. **XSS Koruması (Çok Katmanlı)**

#### Katman 1: Model Observer'lar
- `PostObserver`: Content sanitization
- `ArticleObserver`: Article text sanitization
- `AgencyNewsObserver`: Content sanitization
- `FileObserver`: Title, alt_text, caption escape
- `PostFileObserver`: Title, alt_text, caption escape

#### Katman 2: Service Layer
- `PostCreationService`: Content sanitization
- `PostUpdateService`: Content sanitization
- `ArticleService`: Article text sanitization
- `FileService`: Metadata sanitization
- `PostMediaService`: Gallery metadata sanitization

#### Katman 3: Livewire Component'ler
- `FileUpload`: getClientOriginalName() escape
- `PostEdit`: File metadata sanitization
- `PostCreateGallery`: Gallery data sanitization

#### Katman 4: Blade Template
- `{!! !!}`: Sadece sanitize edilmiş HTML için
- `{{ }}`: Plain text için (her zaman)

### 2. **Dosya Yükleme Güvenliği**

#### `FileUploadService`
- MIME type validation (çift katmanlı: getMimeType + finfo)
- Content scanning (zararlı pattern taraması)
- Extension whitelist
- UUID tabanlı dosya adlandırma
- File size limits

### 3. **Yetkilendirme**

#### Spatie Permission
- Rol bazlı erişim kontrolü
- Permission bazlı erişim kontrolü
- Policy bazlı model yetkilendirme

#### Middleware
- `CheckPermission`: Permission kontrolü
- `RoleMiddleware`: Rol kontrolü
- `RoleOrPermissionMiddleware`: Rol veya permission kontrolü

---

## 🧪 Test Yapısı

### Test Dosyaları

```
tests/
├── Feature/                # Feature test'leri
│   ├── HtmlSanitizerTest.php      # XSS koruması testleri
│   ├── ExceptionHandlingTest.php # Exception handling testleri
│   └── Modules/                   # Modül test'leri
├── Unit/                   # Unit test'leri
│   └── PostEnumsTest.php   # Enum test'leri
└── Fixtures/               # Test fixture'ları
    └── TestToastComponent.php # Test component'i
```

### Test Araçları
- **Pest**: Modern PHP test framework
- **PHPUnit**: Backend test framework
- **Laravel Testing**: Database, HTTP, Livewire test utilities

---

## 📊 Veritabanı Yapısı

### Ana Tablolar

#### Core Tables
- `users`: Kullanıcılar
- `modules`: Modül yönetimi
- `menu_items`: Menü yapısı

#### Permission Tables (Spatie)
- `roles`: Roller
- `permissions`: İzinler
- `model_has_roles`: Model-rol ilişkileri
- `model_has_permissions`: Model-izin ilişkileri
- `role_has_permissions`: Rol-izin ilişkileri

#### Modül Tabloları
Her modül kendi tablolarına sahip:
- `posts`: Post'lar
- `articles`: Makaleler
- `categories`: Kategoriler
- `files`: Dosyalar
- `comments`: Yorumlar
- `newsletters`: Bültenler
- `user_logs`: Sistem logları
- vb.

---

## 🚀 Performans Optimizasyonları

### 1. **Query Optimizasyonu**

#### Column Projection
```php
// Önce
Post::with(['author', 'primaryFile'])->get();

// Sonra
Post::select(['id', 'title', 'status', 'created_at'])
    ->with(['author:id,name', 'primaryFile:id,file_path'])
    ->get();
```

#### Optimize Edilmiş Servisler
- `PostQueryService`: Sadece gerekli kolonlar
- `LogService`: Column projection
- `AgencyNewsService`: Gereksiz kolonlar kaldırıldı
- `LastminuteService`: Optimize edilmiş select

### 2. **Selection Management**

#### visibleIds Pattern
```php
// DB query'siz selection yönetimi
public array $visibleStockIds = [];

protected function getVisibleItemIds(): array
{
    return $this->visibleStockIds; // Array'den alınır, DB'ye gitmez
}
```

#### Array-based Calculations
```php
// array_diff() ile selectAll hesaplama
$diff = array_diff($visibleIds, $this->selectedPosts);
$this->selectAll = empty($diff);
```

### 3. **Cache Stratejileri**

#### Menu Cache
- `MenuHelper`: 10 dakikalık cache
- Rol bazlı cache key'leri

#### Content Suggestions Cache
- `ContentSuggestionService`: 30 dakikalık cache blokları

---

## 🔧 Konfigürasyon Dosyaları

### Ana Konfigürasyonlar

#### `config/modules.php`
- Modül namespace: `Modules`
- Modül discovery ayarları
- Stub dosyaları konfigürasyonu

#### `config/permission.php`
- Spatie Permission ayarları
- Cache ayarları

#### `phpstan.neon`
- PHPStan seviye 5
- Ignore rules (Laravel facade'ler, test helper'lar)
- Path'ler ve exclude'lar

---

## 📝 Route Yapısı

### Route Grupları

#### Admin Routes
- Prefix: `/admin/*`
- Middleware: `auth`, `permission:*`
- Modül bazlı route'lar

#### Web Routes
- Genel web route'ları
- Authentication route'ları
- Dashboard route'u

#### API Routes
- API endpoint'leri (opsiyonel)
- Modül bazlı API route'ları

---

## 🎯 Kullanım Senaryoları

### Senaryo 1: Yeni Post Oluşturma

1. **Kullanıcı:** `PostCreateNews` component'ine girer
2. **Form Doldurma:** Title, content, category seçimi
3. **Submit:** `PostsService::create()` çağrılır
4. **Sanitization:** `PostCreationService` content'i sanitize eder
5. **Observer:** `PostObserver::creating()` tekrar sanitize eder (defense in depth)
6. **Database:** Post kaydedilir
7. **Relations:** Category ve tag'ler sync edilir
8. **Response:** Toast notification gösterilir

### Senaryo 2: Post Listesi Filtreleme

1. **Kullanıcı:** `PostIndex` component'ine girer
2. **Initial Load:** `PostQueryService::getFilteredQuery()` optimize sorgu çalıştırır
3. **Filter Değişikliği:** Kullanıcı status filtresini değiştirir
4. **HasSearchAndFilters:** `onFilterUpdated()` tetiklenir
5. **Selection Reset:** `HasBulkActions::resetSelection()` çağrılır
6. **Page Reset:** `resetPage()` çağrılır
7. **Re-query:** Yeni filtrelerle sorgu tekrar çalıştırılır
8. **Render:** Component yeniden render edilir

### Senaryo 3: Toplu İşlem

1. **Kullanıcı:** `PostIndex`'te birkaç post seçer
2. **Selection:** `HasBulkActions` selection'ı yönetir (visibleIds array'den)
3. **Bulk Action:** "Delete" seçilir ve uygulanır
4. **Permission Check:** `Gate::authorize('delete posts')`
5. **Service:** `PostBulkActionService::handle()` çağrılır
6. **Execution:** Seçili post'lar silinir
7. **Selection Reset:** `resetSelection()` çağrılır
8. **Response:** Toast notification gösterilir

### Senaryo 4: Exception Handling

1. **Exception:** Herhangi bir yerde exception fırlatılır
2. **Catch:** `try-catch` bloğunda yakalanır
3. **Handler:** `HandlesExceptionsWithToast::handleException()` çağrılır
4. **Logging:** Detaylı log context ile `Log::error()` çağrılır
5. **Report:** Laravel exception handler'a report edilir (Sentry, Bugsnag)
6. **User Notification:** `SupportsToastErrors` kontrolü yapılır
7. **Toast:** `toastError()` çağrılır ve frontend'e event gönderilir
8. **Frontend:** Alpine.js toast gösterir

---

## 🔗 Bağımlılık İlişkileri

### Trait Kullanım Matrisi

| Component | HasBulkActions | HasSearchAndFilters | HasColumnPreferences | InteractsWithToast | HandlesExceptionsWithToast |
|-----------|----------------|---------------------|----------------------|-------------------|---------------------------|
| PostIndex | ✅ | ✅ | ✅ | ✅ | ✅ |
| FileIndex | ✅ | ✅ | ❌ | ✅ | ✅ |
| LogIndex | ✅ | ✅ | ❌ | ✅ | ✅ |
| StockIndex | ✅ | ✅ | ✅ | ✅ | ✅ |
| AgencyNewsIndex | ❌ | ✅ | ❌ | ✅ | ✅ |
| LastminuteIndex | ❌ | ✅ | ❌ | ✅ | ❌ |
| ArticleIndex | ❌ | ❌ | ❌ | ✅ | ✅ |
| AuthorIndex | ❌ | ❌ | ❌ | ✅ | ✅ |

### Interface Implementasyonu

| Component | SupportsSelectionReset | SupportsToastErrors |
|-----------|------------------------|---------------------|
| PostIndex | ✅ | ✅ |
| FileIndex | ✅ | ✅ |
| LogIndex | ✅ | ✅ |
| StockIndex | ✅ | ✅ |
| AgencyNewsIndex | ❌ | ✅ |
| ArticleIndex | ❌ | ✅ |
| (40+ component) | ❌ | ✅ |

---

## 📚 Servis Kullanım Haritası

### PostsService (Orchestrator)
```
PostsService
├── PostCreationService (create)
├── PostUpdateService (update)
├── PostQueryService (getFilteredQuery)
├── PostMediaService (media operations)
└── PostBulkActionService (bulk actions)
```

### FileUploadService
```
FileUploadService
├── PostCreateGallery (gallery uploads)
├── PostEdit (file updates)
├── FileUpload (general file uploads)
└── PostMediaService (delegates to FileUploadService)
```

### Sanitizer
```
Sanitizer
├── PostObserver (content sanitization)
├── ArticleObserver (article text sanitization)
├── AgencyNewsObserver (content sanitization)
├── FileObserver (metadata escape)
├── PostFileObserver (metadata escape)
├── PostCreationService (content sanitization)
├── PostUpdateService (content sanitization)
├── ArticleService (article text sanitization)
├── FileService (metadata sanitization)
├── PostMediaService (gallery metadata sanitization)
└── FileUpload (filename escape)
```

---

## 🎨 UI/UX Pattern'leri

### 1. **Toast Notifications**
- **Kullanım:** Tüm başarı/hata mesajları için
- **Implementation:** `InteractsWithToast` trait
- **Frontend:** Alpine.js toast component
- **Persistence:** Redirect sonrası session flash

### 2. **Modal Yönetimi**
- **Kullanım:** Form'lar, onay dialog'ları
- **Implementation:** `InteractsWithModal` trait
- **Frontend:** Alpine.js modal component

### 3. **Bulk Actions**
- **Kullanım:** Listing sayfalarında toplu işlemler
- **Implementation:** `HasBulkActions` trait
- **UI:** Checkbox'lar, select all, action dropdown

### 4. **Search & Filters**
- **Kullanım:** Listing sayfalarında arama ve filtreleme
- **Implementation:** `HasSearchAndFilters` trait
- **UI:** Search input, filter dropdown'ları

### 5. **Column Preferences**
- **Kullanım:** Tablo kolon görünürlüğü
- **Implementation:** `HasColumnPreferences` trait
- **UI:** Column visibility toggle'ları
- **Storage:** User model'inde `table_columns` JSON field

---

## 🔄 Event ve Listener Yapısı

### Authentication Events
- **Listener:** `LogAuthenticationEvents`
- **Event'ler:** Login, Logout, Failed login
- **İşlev:** Aktivite loglama

### Model Events (Observer'lar)
- **PostObserver:** creating, updating
- **ArticleObserver:** creating, updating
- **AgencyNewsObserver:** creating, updating
- **FileObserver:** creating, updating
- **PostFileObserver:** creating, updating

---

## 📦 Paket Bağımlılıkları

### Ana Paketler
- **laravel/framework:** ^12.0
- **livewire/livewire:** ^3.6
- **nwidart/laravel-modules:** ^12.0
- **spatie/laravel-permission:** ^6.21

### Development Paketler
- **larastan/larastan:** ^3.7 (PHPStan Laravel extension)
- **phpstan/phpstan:** ^2.1
- **laravel/pint:** ^1.24
- **pestphp/pest:** ^3.8

---

## 🎯 Best Practices

### 1. **Service Layer Pattern**
- Business logic servis katmanında
- Controller'lar sadece HTTP isteklerini yönetir
- Livewire component'ler servisleri kullanır

### 2. **Observer Pattern**
- Model lifecycle event'leri için observer'lar
- Sanitization, audit logging, business logic

### 3. **Trait Composition**
- Ortak işlevsellik trait'lerde
- Interface'ler ile type safety
- Component'ler trait'leri compose eder

### 4. **Query Optimization**
- Column projection (`select()`)
- Relation projection (`with(['relation:id,name'])`)
- Gereksiz kolon yüklemelerini önleme

### 5. **Defense in Depth (Güvenlik)**
- Çok katmanlı XSS koruması
- Observer + Service + Component katmanlarında sanitization
- Blade template'te safe rendering

### 6. **Type Safety**
- PHPStan level 5
- Interface'ler ile contract'lar
- Type hints ve PHPDoc

---

## 📖 Kod Örnekleri

### Yeni Modül Oluşturma

```bash
php artisan module:make NewModule
```

Bu komut aşağıdaki yapıyı oluşturur:
- Controller'lar
- Livewire component'leri
- Model'ler
- Migration'lar
- View'lar
- Route'lar
- Service provider'lar

### Yeni Listing Component Oluşturma

```php
namespace Modules\NewModule\Livewire;

use App\Contracts\SupportsSelectionReset;
use App\Contracts\SupportsToastErrors;
use App\Livewire\Concerns\HasBulkActions;
use App\Livewire\Concerns\HasSearchAndFilters;
use App\Livewire\Concerns\InteractsWithToast;
use App\Traits\HandlesExceptionsWithToast;
use Livewire\Component;

class NewModuleIndex extends Component 
    implements SupportsSelectionReset, SupportsToastErrors
{
    use HandlesExceptionsWithToast, InteractsWithToast;
    use HasBulkActions, HasSearchAndFilters;

    protected function getVisibleItemIds(): array
    {
        return $this->visibleItemIds ?? [];
    }

    protected function getSelectedItemsPropertyName(): string
    {
        return 'selectedItems';
    }

    public function applyBulkAction(): void
    {
        // Bulk action logic
    }
}
```

### Yeni Servis Oluşturma

```php
namespace Modules\NewModule\Services;

use Modules\NewModule\Models\NewModuleModel;

class NewModuleService
{
    public function create(array $data): NewModuleModel
    {
        // Business logic
        return NewModuleModel::create($data);
    }

    public function getFilteredQuery(array $filters)
    {
        $query = NewModuleModel::query()
            ->select(['id', 'title', 'status', 'created_at']); // Column projection

        // Apply filters
        if ($filters['search']) {
            $query->where('title', 'like', "%{$filters['search']}%");
        }

        return $query;
    }
}
```

---

## 🗺️ Dosya Yolu Referansları

### Ana Dizinler
- **app/**: Ana uygulama kodu
- **Modules/**: Modüler yapı
- **config/**: Konfigürasyonlar
- **database/**: Migration'lar ve seed'ler
- **resources/**: View'lar, CSS, JS
- **routes/**: Route tanımları
- **tests/**: Test dosyaları

### Modül Dizinleri
- **Modules/ModuleName/app/**: Modül PHP kodu
- **Modules/ModuleName/resources/**: Modül view'ları ve asset'ler
- **Modules/ModuleName/routes/**: Modül route'ları
- **Modules/ModuleName/database/**: Modül migration'ları

### Livewire Component Yolları
- **Component Class:** `Modules\ModuleName\Livewire\ComponentName`
- **View File:** `Modules/ModuleName/resources/views/livewire/component-name.blade.php`
- **Route:** Modül route dosyasında tanımlanır

---

## 🔍 Debugging ve Geliştirme

### Log Dosyaları
- **Location:** `storage/logs/laravel.log`
- **Format:** Laravel standard log format
- **Context:** Exception handling ile detaylı context

### PHPStan Analizi
```bash
php -d memory_limit=512M vendor/bin/phpstan analyse --level=5
```

### Code Style
```bash
./vendor/bin/pint
```

### Test Çalıştırma
```bash
php artisan test
```

---

## 📝 Notlar

### Önemli Dosyalar
- **composer.json**: Paket bağımlılıkları ve autoload
- **phpstan.neon**: Statik analiz konfigürasyonu
- **config/modules.php**: Modül sistemi ayarları
- **app/Providers/AppServiceProvider.php**: Observer kayıtları

### Önemli Pattern'ler
- **Service Layer**: Business logic servislerde
- **Observer Pattern**: Model lifecycle event'leri
- **Trait Composition**: Ortak işlevsellik
- **Interface Contracts**: Type safety
- **Defense in Depth**: Çok katmanlı güvenlik

---

**Son Güncelleme:** 2025-01-27  
**Versiyon:** 1.0.0

