<?php

namespace Modules\Articles\Domain\Repositories;

use Modules\Articles\Models\Article;

/**
 * Article Repository Interface
 *
 * Article data access işlemlerini soyutlar.
 */
interface ArticleRepositoryInterface
{
    /**
     * Find article by ID
     */
    public function findById(int $articleId): ?Article;

    /**
     * Find article by slug
     */
    public function findBySlug(string $slug): ?Article;

    /**
     * Create a new article
     */
    public function create(array $data): Article;

    /**
     * Update an existing article
     */
    public function update(Article $article, array $data): Article;

    /**
     * Delete an article
     */
    public function delete(Article $article): bool;

    /**
     * Check if slug exists
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool;

    /**
     * Get query builder for articles
     */
    public function getQuery(): \Illuminate\Database\Eloquent\Builder;
}
