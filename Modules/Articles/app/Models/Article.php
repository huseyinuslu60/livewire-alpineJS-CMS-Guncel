<?php

namespace Modules\Articles\Models;

use App\Traits\AuditFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $article_id
 * @property int|null $author_id
 * @property string $title
 * @property string|null $summary
 * @property string|null $article_text
 * @property string|null $published_at
 * @property int $hit
 * @property bool $show_on_mainpage
 * @property bool $is_commentable
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property string $status
 * @property int|null $site_id
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Article ofStatus($status)
 * @method static \Illuminate\Database\Eloquent\Builder|Article sortedLatest($column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder|Article ofAuthor($authorId)
 * @method static \Illuminate\Database\Eloquent\Builder|Article search(?string $term)
 * @method static \Illuminate\Database\Eloquent\Builder|Article published()
 * @method static \Illuminate\Database\Eloquent\Builder|Article mainPage()
 * @method static \Illuminate\Database\Eloquent\Builder|Article commentable()
 * @method static \Illuminate\Database\Eloquent\Builder|Article forSite($siteId)
 */
class Article extends Model
{
    use AuditFields, HasFactory, SoftDeletes;

    protected $table = 'articles';

    protected $primaryKey = 'article_id';

    protected $fillable = [
        'author_id',
        'title',
        'summary',
        'published_at',
        'article_text',
        'hit',
        'show_on_mainpage',
        'is_commentable',
        'created_by',
        'updated_by',
        'deleted_by',
        'status',
        'site_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'show_on_mainpage' => 'boolean',
        'is_commentable' => 'boolean',
        'hit' => 'integer',
        'author_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer',
        'site_id' => 'integer',
    ];

    protected $dates = [
        'published_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Article'ın yazarı
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(\Modules\Authors\Models\Author::class, 'author_id');
    }

    /**
     * Article'ın kategorisi
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(\Modules\Categories\Models\Category::class, 'category_id');
    }

    /**
     * Article'ı oluşturan kullanıcı
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Article'ı güncelleyen kullanıcı
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Article'ı silen kullanıcı
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'deleted_by');
    }

    /**
     * Scope: Yayınlanmış makaleler
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope: Ana sayfada gösterilecek makaleler
     */
    public function scopeMainPage($query)
    {
        return $query->where('show_on_mainpage', true);
    }

    /**
     * Scope: Yorumlanabilir makaleler
     */
    public function scopeCommentable($query)
    {
        return $query->where('is_commentable', true);
    }

    /**
     * Scope: Belirli site için makaleler
     */
    public function scopeForSite($query, $siteId)
    {
        return $query->where('site_id', $siteId);
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
                ->orWhere('summary', $likeOp, "%{$safe}%")
                ->orWhere('article_text', $likeOp, "%{$safe}%");
        });
    }

    public function scopeOfStatus($query, $status)
    {
        return (is_null($status) || $status === '') ? $query : $query->where('status', $status);
    }

    public function scopeOfAuthor($query, $authorId)
    {
        return (is_null($authorId) || $authorId === '') ? $query : $query->where('author_id', $authorId);
    }

    public function scopeSortedLatest($query, $column = 'created_at')
    {
        return $query->latest($column);
    }

    /**
     * Makale durumu için accessor
     */
    public function getStatusTextAttribute()
    {
        return match ($this->status) {
            'draft' => 'Pasif',
            'published' => 'Aktif',
            'pending' => 'Beklemede',
            default => 'Bilinmiyor'
        };
    }

    /**
     * Makale durumu için renk
     */
    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'draft' => 'secondary',
            'published' => 'success',
            'pending' => 'info',
            default => 'dark'
        };
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Modules\Articles\Database\Factories\ArticleFactory::new();
    }
}
