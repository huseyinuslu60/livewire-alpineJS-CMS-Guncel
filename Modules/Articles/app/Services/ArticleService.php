<?php

namespace Modules\Articles\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Articles\Models\Article;

class ArticleService
{
    /**
     * Filtreli sorgu oluştur
     *
     * @param  array<string, mixed>  $filters  Filtre parametreleri
     * @param  bool  $canViewAll  Tüm makaleleri görüntüleme yetkisi
     */
    public function getFilteredQuery(array $filters = [], bool $canViewAll = true): Builder
    {
        $query = Article::with(['author', 'creator']);

        // Yetki bazlı kontrol: view all articles yetkisi yoksa sadece kendi makalelerini göster
        if (! $canViewAll) {
            $query->where('author_id', Auth::id());
        }

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['statusFilter'])) {
            $query->ofStatus($filters['statusFilter']);
        }

        // Author filter - sadece view all articles yetkisi olanlar kullanabilir
        if (! empty($filters['authorFilter']) && $canViewAll) {
            $query->ofAuthor($filters['authorFilter']);
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
     * Article oluştur
     *
     * @param  array<string, mixed>  $data  Article verileri
     */
    public function create(array $data): Article
    {
        return DB::transaction(function () use ($data) {
            // Eğer durum published ise ve published_at boşsa, şu anki zamanı ata
            if (($data['status'] ?? '') === 'published' && empty($data['published_at'])) {
                $data['published_at'] = now();
            }

            $article = Article::create($data);

            Log::info('Article created via ArticleService', [
                'article_id' => $article->article_id,
                'title' => $article->title,
                'status' => $article->status,
            ]);

            return $article;
        });
    }

    /**
     * Article güncelle
     *
     * @param  Article  $article  Article modeli
     * @param  array<string, mixed>  $data  Güncellenecek veriler
     */
    public function update(Article $article, array $data): Article
    {
        return DB::transaction(function () use ($article, $data) {
            $article->update($data);

            Log::info('Article updated via ArticleService', [
                'article_id' => $article->article_id,
                'title' => $article->title,
                'status' => $article->status,
            ]);

            return $article->fresh();
        });
    }

    /**
     * Article sil
     *
     * @param  Article  $article  Article modeli
     */
    public function delete(Article $article): void
    {
        DB::transaction(function () use ($article) {
            $articleId = $article->article_id;
            $title = $article->title;

            $article->delete();

            Log::info('Article deleted via ArticleService', [
                'article_id' => $articleId,
                'title' => $title,
            ]);
        });
    }

    /**
     * Article durumunu toggle et
     *
     * @param  Article  $article  Article modeli
     * @return string Yeni durum
     */
    public function toggleStatus(Article $article): string
    {
        return DB::transaction(function () use ($article) {
            $newStatus = match ($article->status) {
                'draft' => 'published',
                'published' => 'draft',
                'pending' => 'published',
                default => 'draft'
            };

            $article->update(['status' => $newStatus]);

            Log::info('Article status toggled via ArticleService', [
                'article_id' => $article->article_id,
                'old_status' => $article->getOriginal('status'),
                'new_status' => $newStatus,
            ]);

            return $newStatus;
        });
    }

    /**
     * Ana sayfa durumunu toggle et
     *
     * @param  Article  $article  Article modeli
     * @return bool Yeni durum
     */
    public function toggleMainPage(Article $article): bool
    {
        return DB::transaction(function () use ($article) {
            $newValue = ! $article->show_on_mainpage;
            $article->update(['show_on_mainpage' => $newValue]);

            Log::info('Article main page status toggled via ArticleService', [
                'article_id' => $article->article_id,
                'show_on_mainpage' => $newValue,
            ]);

            return $newValue;
        });
    }
}
