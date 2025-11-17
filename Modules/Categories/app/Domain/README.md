# Categories Domain Layer

Bu klasör, Categories modülü için Domain-Driven Design (DDD) yapısını içerir.

## Klasör Yapısı

```
Domain/
├── Services/          # Domain Services (İş kuralları)
│   └── CategoryValidator.php
└── ValueObjects/     # Value Objects (Type-safe değerler)
    ├── CategoryStatus.php
    └── CategoryType.php
```

## Domain Services

### CategoryValidator
Category iş kurallarını ve validasyon mantığını yönetir:
- Category name validasyonu (required, max 255)
- Category type validasyonu
- Category status validasyonu

## Value Objects

### CategoryStatus
Category durumlarını type-safe olarak yönetir:
- `active` - Aktif
- `inactive` - Pasif
- `draft` - Taslak

### CategoryType
Category tiplerini type-safe olarak yönetir:
- `news` - Haber
- `gallery` - Galeri
- `video` - Video

## Application Services

Application Services (`app/Services/CategoryService.php`) bu Domain katmanını kullanır:
- `CategoryValidator` ile validasyon yapar
- `CategoryStatus` ve `CategoryType` ValueObjects'lerini kullanabilir
- Use case orchestration'ı yönetir (create, update, delete)

## Kullanım Örneği

```php
use Modules\Categories\Domain\Services\CategoryValidator;
use Modules\Categories\Domain\ValueObjects\CategoryStatus;
use Modules\Categories\Domain\ValueObjects\CategoryType;

// CategoryValidator kullanımı
$validator = app(CategoryValidator::class);
$validator->validate($data);

// CategoryStatus kullanımı
$status = CategoryStatus::fromString('active');
if ($status->isActive()) {
    // Active category logic
}

// CategoryType kullanımı
$type = CategoryType::news();
if ($type->isNews()) {
    // News category logic
}
```

