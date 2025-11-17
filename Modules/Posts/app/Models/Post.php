<?php

namespace Modules\Posts\Models;

use App\Traits\AuditFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $post_id
 * @property int|null $author_id
 * @property string $title
 * @property string $slug
 * @property string|null $summary
 * @property string|null $content
 * @property string $post_type
 * @property string $post_position
 * @property int|null $post_order
 * @property bool $is_comment
 * @property bool $is_mainpage
 * @property string|null $redirect_url
 * @property int $view_count
 * @property string $status
 * @property bool $is_photo
 * @property string|null $agency_name
 * @property int|null $agency_id
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property string|null $embed_code
 * @property bool $in_newsletter
 * @property bool $no_ads
 * @property string|null $gallery_data
 * @property string|null $tags
 * @property string|null $published_date
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 */
class Post extends Model
{
    use AuditFields, HasFactory, SoftDeletes;

    protected $table = 'posts';

    protected $primaryKey = 'post_id';

    // Constants (deprecated - use ValueObjects instead)
    /**
     * @deprecated Use PostType::all() instead
     * @see \Modules\Posts\Domain\ValueObjects\PostType
     */
    public const TYPES = ['news', 'gallery', 'video'];

    public const POSITIONS = ['normal', 'manşet', 'sürmanşet', 'öne çıkanlar'];

    /**
     * @deprecated Use PostStatus::all() instead
     * @see \Modules\Posts\Domain\ValueObjects\PostStatus
     */
    public const STATUSES = ['draft', 'published', 'scheduled'];

    // Türkçe etiketler (deprecated - use ValueObjects instead)
    /**
     * @deprecated Use PostType::allWithLabels() instead
     * @see \Modules\Posts\Domain\ValueObjects\PostType
     */
    public const TYPE_LABELS = [
        'news' => 'Haber',
        'gallery' => 'Galeri',
        'video' => 'Video',
    ];

    /**
     * @deprecated Use PostStatus::allWithLabels() instead
     * @see \Modules\Posts\Domain\ValueObjects\PostStatus
     */
    public const STATUS_LABELS = [
        'draft' => 'Pasif',
        'published' => 'Aktif',
        'scheduled' => 'Zamanlanmış',
    ];

    public const POSITION_LABELS = [
        'normal' => 'Normal',
        'manşet' => 'Manşet',
        'sürmanşet' => 'Sürmanşet',
        'öne çıkanlar' => 'Öne Çıkanlar',
    ];

    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'summary',
        'published_date',
        'content',
        'post_type',
        'post_position',
        'post_order',
        'is_comment',
        'is_mainpage',
        'redirect_url',
        'view_count',
        'status',
        'is_photo',
        'agency_name',
        'agency_id',
        'created_by',
        'updated_by',
        'deleted_by',
        'embed_code',
        'in_newsletter',
        'no_ads',
        'spot_data',
    ];

    protected $casts = [
        'published_date' => 'datetime',
        'is_comment' => 'boolean',
        'is_mainpage' => 'boolean',
        'is_photo' => 'boolean',
        'in_newsletter' => 'boolean',
        'no_ads' => 'boolean',
        'view_count' => 'integer',
        'post_order' => 'integer',
        'spot_data' => 'array',
    ];

    protected $dates = [
        'published_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // Relationships
    public function author(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'author_id', 'id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by', 'id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by', 'id');
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'deleted_by', 'id');
    }

    /**
     * Get the files that belong to the Post.
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class, 'post_id', 'post_id');
    }

    /**
     * Get the primary file for the Post.
     */
    public function primaryFile(): HasOne
    {
        // Galeri için primary=true olan dosyayı al
        if ($this->post_type === 'gallery') {
            return $this->hasOne(File::class, 'post_id', 'post_id')->where('primary', true);
        }

        // News/Video için en son güncellenen dosyayı al (primary field'ı olmayabilir)
        return $this->hasOne(File::class, 'post_id', 'post_id')->orderBy('updated_at', 'desc');
    }

    /**
     * Get the primary file for Gallery posts from content JSON
     */
    public function getPrimaryFileForGallery()
    {
        if ($this->post_type !== 'gallery') {
            return null;
        }

        $content = $this->content;
        if (empty($content)) {
            return null;
        }

        $galleryData = is_string($content) ? json_decode($content, true) : $content;

        if (! is_array($galleryData) || empty($galleryData)) {
            return null;
        }

        // Primary olan dosyayı bul
        foreach ($galleryData as $fileData) {
            if (isset($fileData['is_primary']) && $fileData['is_primary'] === true) {
                return (object) [
                    'file_path' => $fileData['file_path'] ?? '',
                    'alt_text' => $fileData['alt_text'] ?? '',
                    'is_image' => true, // Galeri dosyaları her zaman resim
                ];
            }
        }

        // Primary bulunamazsa ilk dosyayı döndür
        if (count($galleryData) > 0) {
            $firstFile = $galleryData[0];

            return (object) [
                'file_path' => $firstFile['file_path'] ?? '',
                'alt_text' => $firstFile['alt_text'] ?? '',
                'is_image' => true,
            ];
        }

        return null;
    }

    /**
     * Get the categories that belong to the Post.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            \Modules\Categories\Models\Category::class,
            'posts_categories',
            'post_id',
            'category_id',
            'post_id',
            'category_id'
        )->withTimestamps();
    }

    /**
     * Get the tags that belong to the Post.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'posts_tags',
            'post_id',
            'tag_id',
            'post_id',
            'tag_id'
        )->withTimestamps();
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('published_date', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('post_type', $type);
    }

    public function scopeByPosition($query, $position)
    {
        return $query->where('post_position', $position);
    }

    public function scopeMainPage($query)
    {
        return $query->where('is_mainpage', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('post_order', 'asc')
            ->orderBy('published_date', 'desc');
    }

    public function scopePopular($query)
    {
        return $query->orderBy('view_count', 'desc');
    }

    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        $safe = str_replace(['%', '_'], ['\\%', '\\_'], $term);
        $driver = $query->getModel()->getConnection()->getDriverName();
        $likeOp = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';

        return $query->where(function ($q) use ($safe, $likeOp) {
            $q->where('title', $likeOp, "%{$safe}%")
                ->orWhere('slug', $likeOp, "%{$safe}%");
        });
    }

    public function scopeOfType($query, $type)
    {
        return (is_null($type) || $type === '') ? $query : $query->where('post_type', $type);
    }

    public function scopeOfStatus($query, $status)
    {
        return (is_null($status) || $status === '') ? $query : $query->where('status', $status);
    }

    public function scopeOfEditor($query, $editorId)
    {
        return (is_null($editorId) || $editorId === '') ? $query : $query->where('created_by', $editorId);
    }

    public function scopeInCategory($query, $categoryId)
    {
        return (is_null($categoryId) || $categoryId === '')
            ? $query
            : $query->whereRelation('categories', 'categories.id', $categoryId);
    }

    public function scopeSortedLatest($query, $column = 'created_at')
    {
        return $query->latest($column);
    }

    /**
     * Newsletter için genişletilmiş arama (title, summary, content)
     */
    public function scopeSearchForNewsletter($query, ?string $term)
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        $safe = str_replace(['%', '_'], ['\\%', '\\_'], $term);
        $driver = $query->getModel()->getConnection()->getDriverName();
        $likeOp = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';

        return $query->where(function ($q) use ($safe, $likeOp) {
            $q->where('title', $likeOp, "%{$safe}%")
                ->orWhere('summary', $likeOp, "%{$safe}%")
                ->orWhere('content', $likeOp, "%{$safe}%");
        });
    }

    // Accessors & Mutators
    public function getExcerptAttribute()
    {
        return $this->summary ?: \Str::limit(strip_tags($this->content), 150);
    }

    public function getIsPublishedAttribute()
    {
        return $this->status === 'published' &&
               $this->published_date &&
               $this->published_date <= now();
    }

    public function getFormattedPublishedDateAttribute()
    {
        if (! $this->published_date) {
            return null;
        }

        return is_string($this->published_date)
            ? \Carbon\Carbon::parse($this->published_date)->format('d.m.Y H:i')
            : $this->published_date->format('d.m.Y H:i');
    }

    /**
     * Get gallery data from content (for gallery posts)
     */
    public function getGalleryDataAttribute()
    {
        if ($this->post_type !== 'gallery') {
            return [];
        }

        // Content'den JSON parse et
        $content = $this->content;
        if (empty($content)) {
            return [];
        }

        // JSON formatında olup olmadığını kontrol et
        $decoded = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // Eski format (HTML) ise boş array döndür
        return [];
    }

    /**
     * Set gallery data to content (for gallery posts)
     */
    public function setGalleryDataAttribute($value)
    {
        if ($this->post_type === 'gallery') {
            $this->content = json_encode($value, JSON_UNESCAPED_UNICODE);
        }
    }

    // Methods
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    public function publish()
    {
        $this->update([
            'status' => 'published',
            'published_date' => now(),
        ]);
    }

    public function archive()
    {
        $this->update(['status' => 'archived']);
    }

    public function setMainPage($value = true)
    {
        $this->update(['is_mainpage' => $value]);
    }

    // Türkçe etiket metodları
    public function getTypeLabel()
    {
        return self::TYPE_LABELS[$this->post_type] ?? ucfirst($this->post_type);
    }

    public function getStatusLabel()
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public static function getTypeLabels()
    {
        return self::TYPE_LABELS;
    }

    public static function getStatusLabels()
    {
        return self::STATUS_LABELS;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Modules\Posts\Database\Factories\PostFactory::new();
    }

    /**
     * Get original image path from spot_data
     */
    public function getOriginalImagePath(): ?string
    {
        if (!is_array($this->spot_data) || !isset($this->spot_data['image']['original']['path'])) {
            return null;
        }
        return $this->spot_data['image']['original']['path'];
    }

    /**
     * Get image variant data (desktop, mobile, etc.)
     */
    public function getImageVariant(string $variant): ?array
    {
        if (!is_array($this->spot_data) || !isset($this->spot_data['image']['variants'][$variant])) {
            return null;
        }
        $variantData = $this->spot_data['image']['variants'][$variant];
        return is_array($variantData) ? $variantData : null;
    }

    /**
     * Get image crop data for a specific variant
     */
    public function getImageCrop(string $variant): ?array
    {
        if (!is_array($this->spot_data) || !isset($this->spot_data['image']['variants'][$variant]['crop'])) {
            return null;
        }
        $crop = $this->spot_data['image']['variants'][$variant]['crop'];
        return is_array($crop) ? $crop : null;
    }

    /**
     * Get image effects (brightness, contrast, blur, etc.)
     */
    public function getImageEffects(): ?array
    {
        if (!is_array($this->spot_data) || !isset($this->spot_data['image']['effects'])) {
            return null;
        }
        $effects = $this->spot_data['image']['effects'];
        return is_array($effects) ? $effects : null;
    }

    /**
     * Get image metadata (alt, credit, source, etc.)
     */
    public function getImageMeta(): ?array
    {
        if (!is_array($this->spot_data) || !isset($this->spot_data['image']['meta'])) {
            return null;
        }
        $meta = $this->spot_data['image']['meta'];
        return is_array($meta) ? $meta : null;
    }

    /**
     * Get all spot_data image data
     */
    public function getSpotImageData(): ?array
    {
        if (!is_array($this->spot_data) || !isset($this->spot_data['image'])) {
            return null;
        }
        $image = $this->spot_data['image'];
        return is_array($image) ? $image : null;
    }

    /**
     * Migrate legacy image data to spot_data format
     * This method is called when spot_data is empty but legacy fields exist
     */
    public function migrateLegacyImageDataToSpotData(): void
    {
        if (!empty($this->spot_data) && isset($this->spot_data['image'])) {
            // Already migrated
            return;
        }

        $spotData = $this->spot_data ?? [];

        // Try to get image from primaryFile relationship
        $primaryFile = $this->primaryFile;
        if ($primaryFile) {
            $spotData['image'] = [
                'original' => [
                    'path' => $primaryFile->file_path,
                    'width' => null,
                    'height' => null,
                    'hash' => null,
                ],
                'variants' => [
                    'desktop' => [
                        'crop' => [], // Empty array, not null
                        'focus' => 'center',
                    ],
                    'mobile' => [
                        'crop' => [], // Empty array, not null
                        'focus' => 'center',
                    ],
                ],
                'effects' => [],
                'meta' => [
                    'alt' => $primaryFile->alt_text ?? null,
                    'credit' => null,
                    'source' => null,
                ],
            ];

            $this->spot_data = $spotData;
            $this->saveQuietly(); // Save without triggering events
        }
    }
}
