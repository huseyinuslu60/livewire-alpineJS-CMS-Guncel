# XSS Koruması Dokümantasyonu

## Genel Bakış

Bu projede HTML içeren içerik alanları için kapsamlı bir XSS (Cross-Site Scripting) koruma sistemi uygulanmıştır.

## Tespit Edilen HTML İçeren Alanlar

1. **Posts Model**: `content` (longText)
2. **Articles Model**: `article_text` (text)
3. **AgencyNews Model**: `content` (text)

## Uygulanan Çözümler

### 1. Sanitizer Service

**Konum**: `app/Support/Sanitizer.php`

**Özellikler**:
- Whitelist yaklaşımı: Sadece izin verilen HTML tag'leri korunur
- Tehlikeli tag'lerin kaldırılması: `<script>`, `<iframe>`, `<object>`, `<embed>`, vb.
- Tehlikeli protokollerin kaldırılması: `javascript:`, `data:`, `vbscript:`, vb.
- Event handler'ların kaldırılması: `onclick`, `onerror`, vb.
- CSS injection koruması: `style` attribute'ları kaldırılır
- Güvenli link yönetimi: `target="_blank"` için otomatik `rel="noopener"` ekleme

**İzin Verilen Tag'ler**:
- Paragraf ve metin: `p`, `br`, `hr`, `strong`, `em`, `b`, `i`, `u`, `s`
- Başlıklar: `h1`, `h2`, `h3`, `h4`, `h5`, `h6`
- Listeler: `ul`, `ol`, `li`
- Alıntılar: `blockquote`, `pre`, `code`
- Linkler ve medya: `a`, `img`
- Yapısal: `div`, `span`
- Tablolar: `table`, `thead`, `tbody`, `tr`, `td`, `th`

### 2. Model Observer'lar

**Konum**: `app/Observers/`

- `PostObserver.php`: Post oluşturma/güncelleme sırasında `content` alanını sanitize eder
- `ArticleObserver.php`: Article oluşturma/güncelleme sırasında `article_text` alanını sanitize eder
- `AgencyNewsObserver.php`: AgencyNews oluşturma/güncelleme sırasında `content` alanını sanitize eder

**Kayıt**: `app/Providers/AppServiceProvider.php` içinde kayıtlıdır.

### 3. Service Katmanı Koruması

**Post Services**:
- `PostCreationService::create()`: Content sanitize edilir
- `PostUpdateService::update()`: Content sanitize edilir

**Article Service**:
- `ArticleService::create()`: `article_text` sanitize edilir
- `ArticleService::update()`: `article_text` sanitize edilir

### 4. Backward Compatibility

**Sanitizer**: `app/Support/Sanitizer.php`
- Geliştirilmiş ve güçlendirilmiş sanitization özellikleri
- Mevcut `sanitizeHtml()` metodu korunmuştur

## Blade Template Kullanımı

### Güvenli Render

HTML içerik alanları sanitize edildiği için `{!! !!}` kullanılabilir:

```blade
{!! $post->content !!}
{!! $article->article_text !!}
{!! $agencyNews->content !!}
```

### Düz Metin Alanlar

Başlık, özet gibi düz metin alanlar için **kesinlikle** `{{ }}` kullanılmalı:

```blade
{{ $post->title }}
{{ $post->summary }}
{{ $article->title }}
```

**Asla** `{!! !!}` kullanmayın çünkü bu alanlar sanitize edilmez!

## Test Senaryoları

**Konum**: `tests/Feature/HtmlSanitizerTest.php`

Test edilen saldırı senaryoları:
- `<script>` tag'leri
- `<iframe>` tag'leri
- `javascript:` protokolü
- `data:` protokolü
- Event handler'lar (`onclick`, `onerror`, vb.)
- CSS injection (`style` attribute)
- `<object>` ve `<embed>` tag'leri
- Model observer'ların çalışması

## Kullanım Örnekleri

### Manuel Sanitize

```php
use App\Support\Sanitizer;

$cleanHtml = Sanitizer::sanitizeHtml($userInput);
```

### Otomatik Sanitize (Observer)

```php
// Post oluştururken otomatik sanitize edilir
$post = Post::create([
    'title' => 'Test',
    'content' => '<script>alert("XSS")</script><p>Safe</p>',
    // content otomatik olarak sanitize edilir
]);

// $post->content artık: '<p>Safe</p>'
```

## Güvenlik Notları

1. **Çift Katmanlı Koruma**: Hem observer'lar hem de service katmanında sanitize yapılır
2. **Whitelist Yaklaşımı**: Sadece izin verilen tag'ler korunur, diğerleri kaldırılır
3. **Protokol Kontrolü**: Tehlikeli protokoller (`javascript:`, `data:`) kaldırılır
4. **Event Handler Koruması**: Tüm `on*` attribute'ları kaldırılır
5. **CSS Injection Koruması**: `style` attribute'ları kaldırılır

## Gelecek İyileştirmeler

- HTMLPurifier gibi daha gelişmiş bir kütüphane entegrasyonu düşünülebilir
- İçerik tipine göre farklı sanitize kuralları (örneğin, admin paneli için daha esnek)
- Sanitize loglama (hangi içeriklerin temizlendiğini kaydetme)

