<?php

namespace Modules\Articles\Services;

use App\Helpers\LogHelper;
use Illuminate\Support\Facades\DB;
use Modules\Articles\Models\Article;

class ArticleService
{
    /**
     * Create a new article
     */
    public function create(array $data): Article
    {
        return DB::transaction(function () use ($data) {
            // Set published_at if status is published and published_at is empty
            if (($data['status'] ?? '') === 'published' && empty($data['published_at'])) {
                $data['published_at'] = now();
            }

            $article = Article::create($data);

            LogHelper::info('Makale oluşturuldu', [
                'article_id' => $article->article_id,
                'title' => $article->title,
            ]);

            return $article;
        });
    }

    /**
     * Update an existing article
     */
    public function update(Article $article, array $data): Article
    {
        try {
            return DB::transaction(function () use ($article, $data) {
                // Set published_at if status is published and published_at is empty
                if (($data['status'] ?? '') === 'published' && empty($data['published_at']) && !$article->published_at) {
                    $data['published_at'] = now();
                }

                $article->update($data);

                LogHelper::info('Makale güncellendi', [
                    'article_id' => $article->article_id,
                    'title' => $article->title,
                ]);

                return $article;
            });
        } catch (\Exception $e) {
            LogHelper::error('ArticleService güncelleme hatası', [
                'article_id' => $article->article_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete an article
     */
    public function delete(Article $article): void
    {
        try {
            DB::transaction(function () use ($article) {
                $article->delete();

                LogHelper::info('Makale silindi', [
                    'article_id' => $article->article_id,
                    'title' => $article->title,
                ]);
            });
        } catch (\Exception $e) {
            LogHelper::error('ArticleService silme hatası', [
                'article_id' => $article->article_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Toggle article status
     */
    public function toggleStatus(Article $article): Article
    {
        try {
            return DB::transaction(function () use ($article) {
                $newStatus = match ($article->status) {
                    'draft' => 'published',
                    'published' => 'draft',
                    'pending' => 'published',
                    default => 'draft'
                };

                $data = ['status' => $newStatus];
                
                // Set published_at if status is published and published_at is empty
                if ($newStatus === 'published' && empty($article->published_at)) {
                    $data['published_at'] = now();
                }

                $article->update($data);

                LogHelper::info('Makale durumu değiştirildi', [
                    'article_id' => $article->article_id,
                    'status' => $article->status,
                ]);

                return $article;
            });
        } catch (\Exception $e) {
            LogHelper::error('ArticleService durum değiştirme hatası', [
                'article_id' => $article->article_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Toggle article mainpage visibility
     */
    public function toggleMainPage(Article $article): Article
    {
        try {
            return DB::transaction(function () use ($article) {
                $article->update(['show_on_mainpage' => !$article->show_on_mainpage]);

                LogHelper::info('Makale ana sayfa görünürlüğü değiştirildi', [
                    'article_id' => $article->article_id,
                    'show_on_mainpage' => $article->show_on_mainpage,
                ]);

                return $article;
            });
        } catch (\Exception $e) {
            LogHelper::error('ArticleService ana sayfa görünürlüğü değiştirme hatası', [
                'article_id' => $article->article_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

