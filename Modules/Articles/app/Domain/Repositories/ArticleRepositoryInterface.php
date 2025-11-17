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
     * 
     * @param int $articleId
     * @return Article|null
     */
    public function findById(int $articleId): ?Article;

    /**
     * Find article by slug
     * 
     * @param string $slug
     * @return Article|null
     */
    public function findBySlug(string $slug): ?Article;

    /**
     * Create a new article
     * 
     * @param array $data
     * @return Article
     */
    public function create(array $data): Article;

    /**
     * Update an existing article
     * 
     * @param Article $article
     * @param array $data
     * @return Article
     */
    public function update(Article $article, array $data): Article;

    /**
     * Delete an article
     * 
     * @param Article $article
     * @return bool
     */
    public function delete(Article $article): bool;

    /**
     * Check if slug exists
     * 
     * @param string $slug
     * @param int|null $excludeId
     * @return bool
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool;
}

