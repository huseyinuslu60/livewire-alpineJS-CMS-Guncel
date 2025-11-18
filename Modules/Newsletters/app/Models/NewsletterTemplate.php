<?php

namespace Modules\Newsletters\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $header_html
 * @property string|null $content_html
 * @property string|null $footer_html
 * @property array|null $styles
 * @property string|null $preview_image
 * @property bool $is_active
 * @property int $sort_order
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|NewsletterTemplate sortedLatest($column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder|NewsletterTemplate search(?string $term)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsletterTemplate active()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsletterTemplate ordered()
 */
class NewsletterTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'header_html',
        'content_html',
        'footer_html',
        'styles',
        'preview_image',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'styles' => 'array',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
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
                ->orWhere('description', $likeOp, "%{$safe}%");
        });
    }

    public function scopeSortedLatest($query, $column = 'created_at')
    {
        return $query->latest($column);
    }
}
