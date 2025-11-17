<?php

namespace Modules\Articles\Domain\Repositories;

use Modules\Articles\Models\Article;

/**
 * Eloquent Article Repository Implementation
 */
class EloquentArticleRepository implements ArticleRepositoryInterface
{
    /**
     * Find article by ID
     */
    public function findById(int $articleId): ?Article
    {
        return Article::find($articleId);
    }

    /**
     * Find article by slug
     */
    public function findBySlug(string $slug): ?Article
    {
        return Article::where('slug', $slug)->first();
    }

    /**
     * Create a new article
     */
    public function create(array $data): Article
    {
        return Article::create($data);
    }

    /**
     * Update an existing article
     */
    public function update(Article $article, array $data): Article
    {
        $article->update($data);
        return $article->fresh();
    }

    /**
     * Delete an article
     */
    public function delete(Article $article): bool
    {
        return $article->delete();
    }

    /**
     * Check if slug exists
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = Article::where('slug', $slug);
        
        if ($excludeId !== null) {
            $query->where('article_id', '!=', $excludeId);
        }
        
        return $query->exists();
    }
}

