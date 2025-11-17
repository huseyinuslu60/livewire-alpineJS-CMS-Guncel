<?php

namespace Modules\Newsletters\Services;

use App\Helpers\LogHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Newsletters\Domain\Events\NewsletterCreated;
use Modules\Newsletters\Domain\Events\NewsletterDeleted;
use Modules\Newsletters\Domain\Events\NewsletterUpdated;
use Modules\Newsletters\Domain\Repositories\NewsletterRepositoryInterface;
use Modules\Newsletters\Domain\Services\NewsletterValidator;
use Modules\Newsletters\Domain\ValueObjects\NewsletterMailStatus;
use Modules\Newsletters\Domain\ValueObjects\NewsletterStatus;
use Modules\Newsletters\Models\Newsletter;
use Modules\Newsletters\Models\NewsletterPost;

class NewsletterService
{
    protected NewsletterValidator $newsletterValidator;
    protected NewsletterRepositoryInterface $newsletterRepository;

    public function __construct(
        ?NewsletterValidator $newsletterValidator = null,
        ?NewsletterRepositoryInterface $newsletterRepository = null
    ) {
        $this->newsletterValidator = $newsletterValidator ?? app(NewsletterValidator::class);
        $this->newsletterRepository = $newsletterRepository ?? app(NewsletterRepositoryInterface::class);
    }

    /**
     * Create a new newsletter
     */
    public function create(array $data, array $postIds = []): Newsletter
    {
        try {
            // Validate newsletter data
            $this->newsletterValidator->validate($data);

            return DB::transaction(function () use ($data, $postIds) {
                // Create newsletter
                $newsletter = $this->newsletterRepository->create($data);

                // Attach posts if provided
                if (!empty($postIds)) {
                    $this->attachPosts($newsletter, $postIds);
                }

                // Fire domain event
                Event::dispatch(new NewsletterCreated($newsletter));

                LogHelper::info('Newsletter oluşturuldu', [
                    'newsletter_id' => $newsletter->newsletter_id,
                    'name' => $newsletter->name,
                    'post_count' => count($postIds),
                ]);

                return $newsletter->load('newsletterPosts');
            });
        } catch (\Exception $e) {
            LogHelper::error('NewsletterService create error', [
                'name' => $data['name'] ?? null,
                'post_count' => count($postIds),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing newsletter
     */
    public function update(Newsletter $newsletter, array $data, ?array $postIds = null): Newsletter
    {
        try {
            // Validate newsletter data
            $this->newsletterValidator->validate($data);

            return DB::transaction(function () use ($newsletter, $data, $postIds) {
                // Update newsletter
                $newsletter = $this->newsletterRepository->update($newsletter, $data);

                // Update posts if provided
                if ($postIds !== null) {
                    $this->syncPosts($newsletter, $postIds);
                }

                // Fire domain event
                $changedAttributes = array_keys($data);
                Event::dispatch(new NewsletterUpdated($newsletter, $changedAttributes));

                LogHelper::info('Newsletter güncellendi', [
                    'newsletter_id' => $newsletter->newsletter_id,
                    'name' => $newsletter->name,
                ]);

                return $newsletter->load('newsletterPosts');
            });
        } catch (\Exception $e) {
            LogHelper::error('NewsletterService güncelleme hatası', [
                'newsletter_id' => $newsletter->newsletter_id,
                'error' => $e->getMessage(),
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
                $this->newsletterRepository->delete($newsletter);

                // Fire domain event
                Event::dispatch(new NewsletterDeleted($newsletter));

                LogHelper::info('Newsletter silindi', [
                    'newsletter_id' => $newsletter->newsletter_id,
                    'name' => $newsletter->name,
                ]);
            });
        } catch (\Exception $e) {
            LogHelper::error('NewsletterService silme hatası', [
                'newsletter_id' => $newsletter->newsletter_id,
                'error' => $e->getMessage(),
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
        try {
            DB::transaction(function () use ($newsletter, $orderedPostIds) {
                foreach ($orderedPostIds as $index => $postId) {
                    NewsletterPost::where('newsletter_id', $newsletter->newsletter_id)
                        ->where('post_id', $postId)
                        ->update(['order' => $index + 1]);
                }
            });
        } catch (\Exception $e) {
            LogHelper::error('NewsletterService reorderPosts error', [
                'newsletter_id' => $newsletter->newsletter_id,
                'post_count' => count($orderedPostIds),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update newsletter status
     */
    public function updateStatus(Newsletter $newsletter, string $status): Newsletter
    {
        try {
            return DB::transaction(function () use ($newsletter, $status) {
                $newsletter->update(['status' => $status]);

                LogHelper::info('Newsletter durumu güncellendi', [
                    'newsletter_id' => $newsletter->newsletter_id,
                    'status' => $status,
                ]);

                return $newsletter;
            });
        } catch (\Exception $e) {
            LogHelper::error('NewsletterService durum güncelleme hatası', [
                'newsletter_id' => $newsletter->newsletter_id,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

