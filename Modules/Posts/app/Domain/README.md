# Posts Domain Layer

Bu klasör, Posts modülü için Domain-Driven Design (DDD) yapısını içerir.

## Klasör Yapısı

```
Domain/
├── Services/          # Domain Services (İş kuralları)
│   └── PostValidator.php
├── ValueObjects/     # Value Objects (Type-safe değerler)
│   ├── PostType.php
│   └── PostStatus.php
└── Repositories/     # Repository Interfaces (Gelecekte kullanılacak)
```

## Domain Services

### PostValidator
Post iş kurallarını ve validasyon mantığını yönetir:
- Post type validasyonu
- Video posts için embed_code kontrolü
- Gallery posts için dosya kontrolü (Livewire'da yapılıyor)

## Value Objects

### PostType
Post tiplerini type-safe olarak yönetir:
- `news` - Haber
- `gallery` - Galeri
- `video` - Video

### PostStatus
Post durumlarını type-safe olarak yönetir:
- `draft` - Taslak
- `published` - Yayında
- `pending` - Beklemede
- `archived` - Arşivlendi

## Application Services

Application Services (`app/Services/PostsService.php`) bu Domain katmanını kullanır:
- `PostValidator` ile validasyon yapar
- `PostType` ve `PostStatus` ValueObjects'lerini kullanabilir
- Use case orchestration'ı yönetir (create, update, delete)

## Kullanım Örneği

```php
use Modules\Posts\Domain\Services\PostValidator;
use Modules\Posts\Domain\ValueObjects\PostType;
use Modules\Posts\Domain\ValueObjects\PostStatus;

// PostValidator kullanımı
$validator = app(PostValidator::class);
$validator->validatePostType($data);

// PostType kullanımı
$postType = PostType::fromString('news');
if ($postType->isNews()) {
    // News post logic
}

// PostStatus kullanımı
$status = PostStatus::published();
if ($status->isPublished()) {
    // Published post logic
}
```

