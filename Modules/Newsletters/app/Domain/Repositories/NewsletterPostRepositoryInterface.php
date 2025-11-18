<?php

namespace Modules\Newsletters\Domain\Repositories;

use Modules\Newsletters\Models\NewsletterPost;

/**
 * Newsletter Post Repository Interface
 *
 * Newsletter post pivot table data access işlemlerini soyutlar.
 */
interface NewsletterPostRepositoryInterface
{
    /**
     * Find newsletter post by ID
     */
    public function findById(int $recordId): ?NewsletterPost;

    /**
     * Find newsletter posts by newsletter ID
     */
    public function findByNewsletterId(int $newsletterId): \Illuminate\Database\Eloquent\Collection;

    /**
     * Create a new newsletter post
     */
    public function create(array $data): NewsletterPost;

    /**
     * Update or create a newsletter post
     */
    public function updateOrCreate(array $attributes, array $values): NewsletterPost;

    /**
     * Update newsletter post
     */
    public function update(NewsletterPost $newsletterPost, array $data): NewsletterPost;

    /**
     * Delete a newsletter post
     */
    public function delete(NewsletterPost $newsletterPost): bool;

    /**
     * Delete newsletter posts by newsletter ID
     *
     * @return int Number of deleted records
     */
    public function deleteByNewsletterId(int $newsletterId): int;

    /**
     * Update newsletter posts by newsletter ID and post ID
     *
     * @return int Number of updated records
     */
    public function updateByNewsletterAndPost(int $newsletterId, int $postId, array $data): int;

    /**
     * Get max order for newsletter
     */
    public function getMaxOrder(int $newsletterId): ?int;

    /**
     * Get query builder for newsletter posts
     */
    public function getQuery(): \Illuminate\Database\Eloquent\Builder;
}
