<?php

namespace Modules\Posts\Domain\Repositories;

use Modules\Posts\Models\Post;

/**
 * Eloquent Post Repository Implementation
 *
 * PostRepositoryInterface'in Eloquent implementasyonu.
 */
class EloquentPostRepository implements PostRepositoryInterface
{
    /**
     * Find post by ID
     */
    public function findById(int $postId): ?Post
    {
        return Post::find($postId);
    }

    /**
     * Find post by slug
     */
    public function findBySlug(string $slug): ?Post
    {
        return Post::where('slug', $slug)->first();
    }

    /**
     * Create a new post
     */
    public function create(array $data): Post
    {
        return Post::create($data);
    }

    /**
     * Update an existing post
     */
    public function update(Post $post, array $data): Post
    {
        $post->update($data);

        return $post->fresh();
    }

    /**
     * Delete a post
     */
    public function delete(Post $post): bool
    {
        return $post->delete();
    }

    /**
     * Check if slug exists
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = Post::where('slug', $slug);

        if ($excludeId !== null) {
            $query->where('post_id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function getQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Post::query();
    }

    /**
     * Find posts by IDs
     */
    public function findByIds(array $postIds): \Illuminate\Database\Eloquent\Collection
    {
        return Post::whereIn('post_id', $postIds)->get();
    }

    /**
     * Bulk update posts by IDs
     */
    public function bulkUpdate(array $postIds, array $data): int
    {
        return Post::whereIn('post_id', $postIds)->update($data);
    }
}
