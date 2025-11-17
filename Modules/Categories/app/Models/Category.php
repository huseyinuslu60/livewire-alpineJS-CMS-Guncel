<?php

namespace Modules\Categories\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property int $category_id
 * @property string $name
 * @property string $slug
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @property string $status
 * @property string $type
 * @property bool $show_in_menu
 * @property int $weight
 * @property int|null $parent_id
 * @property int|null $site_id
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Category extends Model
{
    protected $table = 'categories';

    protected $primaryKey = 'category_id';

    protected $fillable = [
        'name',
        'slug',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'status',
        'type',
        'show_in_menu',
        'weight',
        'parent_id',
        'site_id',
    ];

    protected $casts = [
        'show_in_menu' => 'boolean',
        'weight' => 'integer',
        'parent_id' => 'integer',
        'site_id' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $slugGenerator = app(\App\Services\SlugGenerator::class);
                $slug = $slugGenerator->generate($category->name, self::class, 'slug', 'category_id');
                $category->slug = $slug->toString();
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $slugGenerator = app(\App\Services\SlugGenerator::class);
                $slug = $slugGenerator->generate($category->name, self::class, 'slug', 'category_id', $category->category_id);
                $category->slug = $slug->toString();
            }
        });
    }

    /**
     * Get the parent category.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id', 'category_id');
    }

    /**
     * Get the child categories.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id', 'category_id')->orderBy('weight');
    }

    /**
     * Get all descendants.
     */
    public function descendants(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id', 'category_id')->with('descendants')->orderBy('weight');
    }

    // Site relationship removed - not used

    /**
     * Scope for active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for menu categories.
     */
    public function scopeMenu($query)
    {
        return $query->where('show_in_menu', true);
    }

    /**
     * Scope for root categories (no parent).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope for specific type.
     */
    public function scopeOfType($query, $type)
    {
        return (is_null($type) || $type === '') ? $query : $query->where('type', $type);
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
            $q->where('name', $likeOp, "%{$safe}%")
                ->orWhere('slug', $likeOp, "%{$safe}%");
        });
    }

    public function scopeOfStatus($query, $status)
    {
        return (is_null($status) || $status === '') ? $query : $query->where('status', $status);
    }

    public function scopeSortedLatest($query, $column = 'created_at')
    {
        return $query->latest($column);
    }

    /**
     * Get the full path of the category.
     */
    public function getFullPathAttribute(): string
    {
        $path = collect([$this->name]);
        $parent = $this->parent;

        while ($parent) {
            $path->prepend($parent->name);
            $parent = $parent->parent;
        }

        return $path->implode(' > ');
    }

    /**
     * Get the depth level of the category.
     */
    public function getDepthAttribute(): int
    {
        $depth = 0;
        $parent = $this->parent;

        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }

        return $depth;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Modules\Categories\Database\Factories\CategoryFactory::new();
    }
}
