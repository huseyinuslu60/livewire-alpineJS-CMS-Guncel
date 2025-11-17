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
     * 
     * @param int $postId
     * @return Post|null
     */
    public function findById(int $postId): ?Post;

    /**
     * Find post by slug
     * 
     * @param string $slug
     * @return Post|null
     */
    public function findBySlug(string $slug): ?Post;

    /**
     * Create a new post
     * 
     * @param array $data
     * @return Post
     */
    public function create(array $data): Post;

    /**
     * Update an existing post
     * 
     * @param Post $post
     * @param array $data
     * @return Post
     */
    public function update(Post $post, array $data): Post;

    /**
     * Delete a post
     * 
     * @param Post $post
     * @return bool
     */
    public function delete(Post $post): bool;

    /**
     * Check if slug exists
     * 
     * @param string $slug
     * @param int|null $excludeId
     * @return bool
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool;
}

