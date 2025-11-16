<?php

namespace Modules\Articles\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

            Log::info('Article created', [
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

                Log::info('Article updated', [
                    'article_id' => $article->article_id,
                    'title' => $article->title,
                ]);

                return $article;
            });
        } catch (\Exception $e) {
            Log::error('ArticleService update error:', [
                'article_id' => $article->article_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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

                Log::info('Article deleted', [
                    'article_id' => $article->article_id,
                    'title' => $article->title,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('ArticleService delete error:', [
                'article_id' => $article->article_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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

                Log::info('Article status toggled', [
                    'article_id' => $article->article_id,
                    'status' => $article->status,
                ]);

                return $article;
            });
        } catch (\Exception $e) {
            Log::error('ArticleService toggleStatus error:', [
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

                Log::info('Article mainpage toggled', [
                    'article_id' => $article->article_id,
                    'show_on_mainpage' => $article->show_on_mainpage,
                ]);

                return $article;
            });
        } catch (\Exception $e) {
            Log::error('ArticleService toggleMainPage error:', [
                'article_id' => $article->article_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

