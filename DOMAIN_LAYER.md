# Domain Layer Documentation

Bu dokümantasyon, projede uygulanan Domain-Driven Design (DDD) yapısını açıklar.

## Genel Bakış

Proje, Domain-Driven Design prensiplerine göre yapılandırılmıştır. Bu yapı, iş kurallarını (business rules) kodun merkezine alır ve bakımı kolaylaştırır.

## Klasör Yapısı

Her modül için aşağıdaki Domain Layer yapısı kullanılmaktadır:

```
Modules/{ModuleName}/app/Domain/
├── Services/          # Domain Services (İş kuralları ve validasyon)
│   └── {Module}Validator.php
├── ValueObjects/      # Value Objects (Type-safe değerler)
│   ├── {Module}Status.php
│   └── {Module}Type.php
├── Repositories/      # Repository Interfaces (Data access abstraction)
│   ├── {Module}RepositoryInterface.php
│   └── Eloquent{Module}Repository.php
└── Events/            # Domain Events (Önemli domain olayları)
    ├── {Module}Created.php
    ├── {Module}Updated.php
    └── {Module}Deleted.php
```

## Bileşenler

### 1. Domain Services (Validators)

Domain Services, iş kurallarını ve validasyon mantığını yönetir. Her modül için bir `{Module}Validator` sınıfı bulunur.

**Örnek:**
```php
use Modules\Posts\Domain\Services\PostValidator;

$validator = app(PostValidator::class);
$validator->validatePostType($data);
```

**Sorumluluklar:**
- İş kurallarını uygular
- Veri validasyonu yapar
- Business logic'i kapsüller

### 2. Value Objects

Value Objects, type-safe değerler sağlar ve geçersiz değerlerin kullanılmasını önler.

**Örnek:**
```php
use Modules\Posts\Domain\ValueObjects\PostStatus;

$status = PostStatus::fromString('published');
if ($status->isPublished()) {
    // Published post logic
}
```

**Özellikler:**
- Immutable (değiştirilemez)
- Self-validating (kendi kendini doğrular)
- Type-safe (tip güvenli)

**Mevcut ValueObjects:**
- `PostType`, `PostStatus` (Posts)
- `CategoryType`, `CategoryStatus` (Categories)
- `ArticleStatus` (Articles)
- `NewsletterStatus`, `NewsletterMailStatus` (Newsletters)
- `LastminuteStatus` (Lastminutes)
- `InvestorQuestionStatus` (Banks)

### 3. Repositories

Repositories, data access katmanını soyutlar ve test edilebilirliği artırır.

**Örnek:**
```php
use Modules\Posts\Domain\Repositories\PostRepositoryInterface;

$postRepository = app(PostRepositoryInterface::class);
$post = $postRepository->findById(1);
```

**Avantajlar:**
- Database bağımlılığını azaltır
- Mock'lanabilir (test için)
- Data access mantığını merkezileştirir

**Mevcut Repositories:**
- `PostRepositoryInterface` / `EloquentPostRepository` (Posts)
- `CategoryRepositoryInterface` / `EloquentCategoryRepository` (Categories)

### 4. Domain Events

Domain Events, önemli domain olaylarını temsil eder ve loose coupling sağlar.

**Örnek:**
```php
use Modules\Posts\Domain\Events\PostCreated;

Event::dispatch(new PostCreated($post));
```

**Kullanım:**
- Post oluşturulduğunda `PostCreated` event'i fırlatılır
- Post güncellendiğinde `PostUpdated` event'i fırlatılır
- Post silindiğinde `PostDeleted` event'i fırlatılır

**Event Listener Örneği:**
```php
// EventServiceProvider.php
protected $listen = [
    PostCreated::class => [
        SendNotification::class,
        UpdateCache::class,
    ],
];
```

## Ortak Servisler

### SlugGenerator

Tüm modüller için ortak slug oluşturma servisi:

```php
use App\Services\SlugGenerator;

$slugGenerator = app(SlugGenerator::class);
$slug = $slugGenerator->generate($title, Post::class, 'slug', 'post_id');
```

**Özellikler:**
- Unique slug garantisi
- Model-agnostic (herhangi bir model için kullanılabilir)
- Exclude ID desteği (update işlemleri için)

## Uygulama Servisleri (Application Services)

Application Services (`app/Services/{Module}Service.php`), use case orchestration'ı yönetir:

1. **Validasyon:** Domain Validator'ları kullanır
2. **İş Mantığı:** Domain Services'i çağırır
3. **Data Access:** Repository'leri kullanır
4. **Events:** Domain Events'i fırlatır

**Örnek Akış:**
```php
public function create(array $data): Post
{
    // 1. Validate
    $this->postValidator->validatePostType($data);
    
    // 2. Generate slug
    $slug = $this->slugGenerator->generate(...);
    
    // 3. Create via repository
    $post = $this->postRepository->create($data);
    
    // 4. Fire event
    Event::dispatch(new PostCreated($post));
    
    return $post;
}
```

## Modül Yapısı

### Posts Module

**Domain Layer:**
- `PostValidator` - Post iş kuralları
- `PostType`, `PostStatus` - ValueObjects
- `PostRepositoryInterface` - Data access abstraction
- `PostCreated`, `PostUpdated`, `PostDeleted` - Events

### Categories Module

**Domain Layer:**
- `CategoryValidator` - Category iş kuralları
- `CategoryType`, `CategoryStatus` - ValueObjects
- `CategoryRepositoryInterface` - Data access abstraction
- `CategoryCreated`, `CategoryUpdated`, `CategoryDeleted` - Events

## Best Practices

1. **Domain Logic:** Domain Services ve ValueObjects'te tutulmalı
2. **Data Access:** Repository'ler üzerinden yapılmalı
3. **Events:** Önemli domain olayları için kullanılmalı
4. **Validation:** Domain Validator'larda yapılmalı
5. **Type Safety:** ValueObjects kullanılmalı

## Gelecek Geliştirmeler

- [ ] Tüm modüller için Repository Pattern
- [ ] Daha fazla ValueObject (UserRole, vb.)
- [ ] Event Listener'lar için örnekler
- [ ] Unit test örnekleri
- [ ] CQRS pattern desteği

## Kaynaklar

- [Domain-Driven Design](https://martinfowler.com/bliki/DomainDrivenDesign.html)
- [Value Objects](https://martinfowler.com/bliki/ValueObject.html)
- [Repository Pattern](https://martinfowler.com/eaaCatalog/repository.html)
- [Domain Events](https://martinfowler.com/eaaDev/DomainEvent.html)

