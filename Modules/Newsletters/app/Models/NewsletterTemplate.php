<?php

namespace Modules\Newsletters\Models;

use Illuminate\Database\Eloquent\Model;

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
