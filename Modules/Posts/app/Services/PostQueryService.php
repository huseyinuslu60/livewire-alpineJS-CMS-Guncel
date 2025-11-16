<?php

namespace Modules\Posts\Services;

use Illuminate\Database\Eloquent\Builder;
use Modules\Posts\Models\Post;

class PostQueryService
{
    /**
     * Filtreli sorgu oluştur
     *
     * @param  array<string, mixed>  $filters  Filtre parametreleri
     */
    public function getFilteredQuery(array $filters = []): Builder
    {
        // Optimize: Sadece listing için gerekli kolonları seç
        // Not: post_id primary key olduğu için otomatik gelir, select'e eklenmesine gerek yok
        $query = Post::query()
            ->select([
                'post_id', // Primary key, ama açıkça belirtmek daha iyi
                'title',
                'post_type',
                'view_count',
                'status',
                'is_mainpage',
                'created_at',
                'updated_at',
                'created_by',
                'updated_by',
                'content', // Gallery için getPrimaryFileForGallery() metodunda kullanılıyor
            ])
            // Optimize: Sadece kullanılan relation'ları yükle ve projection uygula
            ->with([
                'creator:id,name,created_at',  // View'de name ve created_at kullanılıyor
                'updater:id,name,updated_at',  // View'de name ve updated_at kullanılıyor
                'categories:category_id,name', // View'de sadece name kullanılıyor
                'primaryFile:file_id,file_path,alt_text', // View'de bu alanlar kullanılıyor (is_image accessor, kolon değil)
            ]);
        // Kaldırılan: 'author' (view'de kullanılmıyor), 'tags' (view'de kullanılmıyor)

        // Arama filtresi
        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Post tipi filtresi
        if (! empty($filters['post_type'])) {
            $query->ofType($filters['post_type']);
        }

        // Durum filtresi
        if (! empty($filters['status'])) {
            $query->ofStatus($filters['status']);
        }

        // Editör filtresi
        if (! empty($filters['editorFilter'])) {
            $query->ofEditor($filters['editorFilter']);
        }

        // Kategori filtresi
        if (! empty($filters['categoryFilter'])) {
            $query->inCategory($filters['categoryFilter']);
        }

        // Sıralama (varsayılan: en yeni)
        $sortBy = $filters['sortBy'] ?? 'post_id';
        $sortDirection = $filters['sortDirection'] ?? 'desc';

        if ($sortBy === 'post_id' && $sortDirection === 'desc') {
            $query->latest('post_id');
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        return $query;
    }
}
