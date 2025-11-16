<?php

namespace Modules\AgencyNews\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $record_id
 * @property string $title
 * @property string|null $summary
 * @property string|null $tags
 * @property int|null $original_id
 * @property int|null $agency_id
 * @property string|null $category
 * @property bool $has_image
 * @property string|null $file_path
 * @property array|null $sites
 * @property string|null $content
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 */
class AgencyNews extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'agency_news';

    protected $primaryKey = 'record_id';

    protected $fillable = [
        'title',
        'summary',
        'tags',
        'original_id',
        'agency_id',
        'category',
        'has_image',
        'file_path',
        'sites',
        'content',
    ];

    protected $casts = [
        'has_image' => 'boolean',
        'agency_id' => 'integer',
        'sites' => 'array',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Agency news'i post'a dönüştür
     */
    public function convertToPost()
    {
        $postData = [
            'title' => $this->title,
            'summary' => $this->summary,
            'content' => $this->content,
            'post_type' => 'news',
            'post_position' => 'normal',
            'status' => 'draft',
            'published_date' => now(),
            'is_comment' => true,
            'is_mainpage' => false,
            'is_photo' => $this->has_image,
            'agency_name' => $this->getAgencyName(),
            'agency_id' => $this->agency_id,
            'in_newsletter' => false,
            'no_ads' => false,
            'author_id' => auth()->id(),
            // Audit fields (created_by, updated_by) are handled by AuditFields trait in Post model
        ];

        // Slug oluştur (unique olması için)
        $baseSlug = \App\Helpers\SystemHelper::createSlug($this->title);
        $slug = $baseSlug;
        $counter = 1;

        // Unique slug oluştur
        while (\Modules\Posts\Models\Post::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        $postData['slug'] = $slug;

        return $postData;
    }

    /**
     * Agency ilişkisi
     */
    public function agency()
    {
        // Agency model'i varsa buraya ekleyebiliriz
        // return $this->belongsTo(Agency::class, 'agency_id');
        return null;
    }

    /**
     * Agency adını al
     */
    public function getAgencyName()
    {
        // Agency model'i varsa:
        // return $this->agency?->name ?? 'Bilinmeyen Agency';

        // Şimdilik hardcoded
        return 'Agency '.$this->agency_id;
    }

    /**
     * Scope: Resimli haberler
     */
    public function scopeWithImage($query)
    {
        return $query->where('has_image', true);
    }

    /**
     * Scope: Belirli agency'den gelen haberler
     */
    public function scopeByAgency($query, $agencyId)
    {
        return $query->where('agency_id', $agencyId);
    }

    /**
     * Scope: Belirli kategorideki haberler
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: Belirli site için haberler
     */
    public function scopeForSite($query, $siteId)
    {
        return $query->whereJsonContains('sites', $siteId);
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
                ->orWhere('content', $likeOp, "%{$safe}%");
        });
    }

    public function scopeOfAgency($query, $agencyId)
    {
        return (is_null($agencyId) || $agencyId === '') ? $query : $query->where('agency_id', $agencyId);
    }

    public function scopeOfCategory($query, $category)
    {
        return (is_null($category) || $category === '') ? $query : $query->where('category', $category);
    }

    public function scopeSortedLatest($query, $column = 'created_at')
    {
        return $query->latest($column);
    }

    /**
     * Resim URL'ini al
     */
    public function getImageUrlAttribute()
    {
        if ($this->file_path) {
            return asset('storage/'.$this->file_path);
        }

        return null;
    }

    /**
     * Tags'ı array olarak al
     */
    public function getTagsArrayAttribute()
    {
        if ($this->tags) {
            return explode(',', $this->tags);
        }

        return [];
    }

    /**
     * Sites'ı array olarak al
     */
    public function getSitesArrayAttribute()
    {
        if ($this->sites) {
            $sites = is_array($this->sites) ? $this->sites : json_decode($this->sites, true);

            return is_array($sites) ? $sites : [];
        }

        return [];
    }

    /**
     * Özet metnini kısalt
     */
    public function getShortSummaryAttribute()
    {
        return \Str::limit($this->summary, 150);
    }

    /**
     * İçeriği kısalt
     */
    public function getShortContentAttribute()
    {
        return \Str::limit(strip_tags($this->content), 200);
    }
}
