<?php

namespace Modules\Newsletters\Domain\Repositories;

use Modules\Newsletters\Models\NewsletterPost;

/**
 * Eloquent Newsletter Post Repository Implementation
 */
class EloquentNewsletterPostRepository implements NewsletterPostRepositoryInterface
{
    /**
     * Find newsletter post by ID
     */
    public function findById(int $recordId): ?NewsletterPost
    {
        return NewsletterPost::find($recordId);
    }

    /**
     * Find newsletter posts by newsletter ID
     */
    public function findByNewsletterId(int $newsletterId): \Illuminate\Database\Eloquent\Collection
    {
        return NewsletterPost::where('newsletter_id', $newsletterId)->get();
    }

    /**
     * Create a new newsletter post
     */
    public function create(array $data): NewsletterPost
    {
        return NewsletterPost::create($data);
    }

    /**
     * Update or create a newsletter post
     */
    public function updateOrCreate(array $attributes, array $values): NewsletterPost
    {
        return NewsletterPost::updateOrCreate($attributes, $values);
    }

    /**
     * Update newsletter post
     */
    public function update(NewsletterPost $newsletterPost, array $data): NewsletterPost
    {
        $newsletterPost->update($data);

        return $newsletterPost->fresh();
    }

    /**
     * Delete a newsletter post
     */
    public function delete(NewsletterPost $newsletterPost): bool
    {
        return $newsletterPost->delete();
    }

    /**
     * Delete newsletter posts by newsletter ID
     */
    public function deleteByNewsletterId(int $newsletterId): int
    {
        return NewsletterPost::where('newsletter_id', $newsletterId)->delete();
    }

    /**
     * Update newsletter posts by newsletter ID and post ID
     */
    public function updateByNewsletterAndPost(int $newsletterId, int $postId, array $data): int
    {
        return NewsletterPost::where('newsletter_id', $newsletterId)
            ->where('post_id', $postId)
            ->update($data);
    }

    /**
     * Get max order for newsletter
     */
    public function getMaxOrder(int $newsletterId): ?int
    {
        return NewsletterPost::where('newsletter_id', $newsletterId)->max('order');
    }

    /**
     * Get query builder for newsletter posts
     */
    public function getQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return NewsletterPost::query();
    }
}
