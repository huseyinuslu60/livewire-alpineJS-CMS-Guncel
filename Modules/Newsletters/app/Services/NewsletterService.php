<?php

namespace Modules\Newsletters\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Newsletters\Models\Newsletter;
use Modules\Newsletters\Models\NewsletterPost;

class NewsletterService
{
    /**
     * Create a new newsletter
     */
    public function create(array $data, array $postIds = []): Newsletter
    {
        return DB::transaction(function () use ($data, $postIds) {
            // Create newsletter
            $newsletter = Newsletter::create($data);

            // Attach posts if provided
            if (!empty($postIds)) {
                $this->attachPosts($newsletter, $postIds);
            }

            Log::info('Newsletter created', [
                'newsletter_id' => $newsletter->newsletter_id,
                'name' => $newsletter->name,
                'post_count' => count($postIds),
            ]);

            return $newsletter->load('newsletterPosts');
        });
    }

    /**
     * Update an existing newsletter
     */
    public function update(Newsletter $newsletter, array $data, ?array $postIds = null): Newsletter
    {
        try {
            return DB::transaction(function () use ($newsletter, $data, $postIds) {
                // Update newsletter
                $newsletter->update($data);

                // Update posts if provided
                if ($postIds !== null) {
                    $this->syncPosts($newsletter, $postIds);
                }

                Log::info('Newsletter updated', [
                    'newsletter_id' => $newsletter->newsletter_id,
                    'name' => $newsletter->name,
                ]);

                return $newsletter->load('newsletterPosts');
            });
        } catch (\Exception $e) {
            Log::error('NewsletterService update error:', [
                'newsletter_id' => $newsletter->newsletter_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete a newsletter
     */
    public function delete(Newsletter $newsletter): void
    {
        try {
            DB::transaction(function () use ($newsletter) {
                // Delete related records first
                $newsletter->newsletterPosts()->delete();
                $newsletter->newsletterLogs()->delete();

                // Delete newsletter
                $newsletter->delete();

                Log::info('Newsletter deleted', [
                    'newsletter_id' => $newsletter->newsletter_id,
                    'name' => $newsletter->name,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('NewsletterService delete error:', [
                'newsletter_id' => $newsletter->newsletter_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Attach posts to newsletter
     */
    public function attachPosts(Newsletter $newsletter, array $postIds, ?int $order = null): void
    {
        $order = $order ?? NewsletterPost::where('newsletter_id', $newsletter->newsletter_id)->max('order') ?? 0;

        foreach ($postIds as $index => $postId) {
            NewsletterPost::updateOrCreate(
                [
                    'newsletter_id' => $newsletter->newsletter_id,
                    'post_id' => $postId,
                ],
                [
                    'order' => $order + $index + 1,
                ]
            );
        }
    }

    /**
     * Sync posts to newsletter (removes old, adds new)
     */
    public function syncPosts(Newsletter $newsletter, array $postIds): void
    {
        // Delete existing posts
        $newsletter->newsletterPosts()->delete();

        // Add new posts with order
        foreach ($postIds as $index => $postId) {
            NewsletterPost::create([
                'newsletter_id' => $newsletter->newsletter_id,
                'post_id' => $postId,
                'order' => $index + 1,
            ]);
        }
    }

    /**
     * Reorder posts in newsletter
     */
    public function reorderPosts(Newsletter $newsletter, array $orderedPostIds): void
    {
        DB::transaction(function () use ($newsletter, $orderedPostIds) {
            foreach ($orderedPostIds as $index => $postId) {
                NewsletterPost::where('newsletter_id', $newsletter->newsletter_id)
                    ->where('post_id', $postId)
                    ->update(['order' => $index + 1]);
            }
        });
    }

    /**
     * Update newsletter status
     */
    public function updateStatus(Newsletter $newsletter, string $status): Newsletter
    {
        try {
            return DB::transaction(function () use ($newsletter, $status) {
                $newsletter->update(['status' => $status]);

                Log::info('Newsletter status updated', [
                    'newsletter_id' => $newsletter->newsletter_id,
                    'status' => $status,
                ]);

                return $newsletter;
            });
        } catch (\Exception $e) {
            Log::error('NewsletterService updateStatus error:', [
                'newsletter_id' => $newsletter->newsletter_id,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

