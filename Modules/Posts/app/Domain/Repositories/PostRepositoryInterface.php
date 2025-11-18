<?php

namespace Modules\Posts\Domain\Repositories;

use Modules\Posts\Models\Post;

/**
 * Post Repository Interface
 *
 * Post data access işlemlerini soyutlar.
 * Business rule: Tüm data access işlemleri bu interface üzerinden yapılmalı.
 */
interface PostRepositoryInterface
{
    /**
     * Find post by ID
     */
    public function findById(int $postId): ?Post;

    /**
     * Find post by slug
     */
    public function findBySlug(string $slug): ?Post;

    /**
     * Create a new post
     */
    public function create(array $data): Post;

    /**
     * Update an existing post
     */
    public function update(Post $post, array $data): Post;

    /**
     * Delete a post
     */
    public function delete(Post $post): bool;

    /**
     * Check if slug exists
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool;

    /**
     * Get query builder for posts
     */
    public function getQuery(): \Illuminate\Database\Eloquent\Builder;

    /**
     * Find posts by IDs
     */
    public function findByIds(array $postIds): \Illuminate\Database\Eloquent\Collection;

    /**
     * Bulk update posts by IDs
     *
     * @return int Number of updated records
     */
    public function bulkUpdate(array $postIds, array $data): int;
}
