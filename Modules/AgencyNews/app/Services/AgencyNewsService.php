<?php

namespace Modules\AgencyNews\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\AgencyNews\Models\AgencyNews;
use Modules\Posts\Models\Post;
use Modules\Posts\Services\PostsService;

class AgencyNewsService
{
    /**
     * Filtreli sorgu oluştur
     *
     * @param  array<string, mixed>  $filters  Filtre parametreleri
     */
    public function getFilteredQuery(array $filters = []): Builder
    {
        $query = AgencyNews::query();

        // Arama filtresi
        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Ajans filtresi
        if (! empty($filters['agencyFilter'])) {
            $query->ofAgency($filters['agencyFilter']);
        }

        // Kategori filtresi
        if (! empty($filters['categoryFilter'])) {
            $query->ofCategory($filters['categoryFilter']);
        }

        // Resim filtresi
        if (isset($filters['imageFilter']) && $filters['imageFilter'] !== '') {
            if ($filters['imageFilter'] === 'yes') {
                $query->where('has_image', true);
            } elseif ($filters['imageFilter'] === 'no') {
                $query->where('has_image', false);
            }
        }

        // Sıralama
        $sortBy = $filters['sortBy'] ?? 'created_at';
        $sortDirection = $filters['sortDirection'] ?? 'desc';

        if ($sortBy === 'created_at' && $sortDirection === 'desc') {
            $query->sortedLatest('created_at');
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        return $query;
    }

    /**
     * AgencyNews'i sil
     *
     * @param  AgencyNews  $news  AgencyNews modeli
     */
    public function delete(AgencyNews $news): void
    {
        DB::transaction(function () use ($news) {
            $recordId = $news->record_id;
            $title = $news->title;

            $news->delete();

            Log::info('AgencyNews deleted via AgencyNewsService', [
                'record_id' => $recordId,
                'title' => $title,
            ]);
        });
    }

    /**
     * AgencyNews'i Post'a dönüştür
     *
     * @param  AgencyNews  $news  AgencyNews modeli
     */
    public function convertToPost(AgencyNews $news): Post
    {
        return DB::transaction(function () use ($news) {
            // Model'deki convertToPost metodunu kullanarak post data hazırla
            $postData = $news->convertToPost();

            // PostsService kullanarak Post oluştur
            $postsService = app(PostsService::class);

            // Tags'ı array'e çevir
            $tagIds = [];
            if ($news->tags) {
                $tagIds = array_filter(array_map('trim', explode(',', $news->tags)));
            }

            // Kategori mapping (opsiyonel - eğer category field'ı varsa)
            $categoryIds = [];
            if ($news->category) {
                // Category name'den category_id bul (opsiyonel)
                // Şimdilik boş bırakıyoruz, gerekirse eklenebilir
            }

            // Post oluştur
            $post = $postsService->create($postData, [], $categoryIds, $tagIds, []);

            Log::info('AgencyNews converted to Post via AgencyNewsService', [
                'agency_news_id' => $news->record_id,
                'post_id' => $post->post_id,
                'title' => $news->title,
            ]);

            return $post;
        });
    }

    /**
     * Filtreleme için agencies listesi oluştur
     *
     * @return \Illuminate\Support\Collection<int, array{id: int, name: string}>
     */
    public function getAgenciesList()
    {
        return AgencyNews::select('agency_id')
            ->whereNotNull('agency_id')
            ->distinct()
            ->pluck('agency_id')
            ->map(function ($id) {
                return ['id' => $id, 'name' => 'Agency '.$id];
            });
    }

    /**
     * Filtreleme için categories listesi oluştur
     *
     * @return array<string>
     */
    public function getCategoriesList(): array
    {
        return AgencyNews::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->toArray();
    }
}
