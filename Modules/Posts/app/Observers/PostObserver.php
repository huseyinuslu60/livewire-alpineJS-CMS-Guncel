<?php

namespace Modules\Posts\Observers;

use App\Traits\HandlesRequestContext;
use Modules\Headline\Services\FeaturedService;
use Modules\Logs\Models\UserLog;
use Modules\Posts\Models\Post;

class PostObserver
{
    use HandlesRequestContext;

    protected $featuredService;

    public function __construct(FeaturedService $featuredService)
    {
        $this->featuredService = $featuredService;
    }

    /**
     * Handle the Post "created" event.
     */
    public function created(Post $post): void
    {
        UserLog::log(
            action: 'create',
            modelType: 'Post',
            modelId: $post->post_id,
            description: "Yeni post oluşturuldu: {$post->title}",
            newValues: $post->toArray(),
            ipAddress: $this->getRequestIp(),
            userAgent: $this->getRequestUserAgent(),
            url: $this->getRequestUrl(),
            method: $this->getRequestMethod()
        );

        $this->syncPostPosition($post);
    }

    /**
     * Handle the Post "updated" event.
     */
    public function updated(Post $post): void
    {
        $changes = $post->getChanges();
        $original = $post->getOriginal();

        if (! empty($changes)) {
            UserLog::log(
                action: 'update',
                modelType: 'Post',
                modelId: $post->post_id,
                description: "Post güncellendi: {$post->title}",
                oldValues: array_intersect_key($original, $changes),
                newValues: $changes,
                ipAddress: $this->getRequestIp(),
                userAgent: $this->getRequestUserAgent(),
                url: $this->getRequestUrl(),
                method: $this->getRequestMethod()
            );
        }

        $this->syncPostPosition($post);
    }

    /**
     * Handle the Post "deleted" event.
     */
    public function deleted(Post $post): void
    {
        UserLog::log(
            action: 'delete',
            modelType: 'Post',
            modelId: $post->post_id,
            description: "Post silindi: {$post->title}",
            oldValues: $post->toArray(),
            ipAddress: $this->getRequestIp(),
            userAgent: $this->getRequestUserAgent(),
            url: $this->getRequestUrl(),
            method: $this->getRequestMethod()
        );

        // Remove from all zones when post is deleted
        $this->featuredService->unpin('manset', 'post', $post->post_id);
        $this->featuredService->unpin('surmanset', 'post', $post->post_id);
        $this->featuredService->unpin('one_cikanlar', 'post', $post->post_id);
    }

    /**
     * Handle the Post "restored" event.
     */
    public function restored(Post $post): void
    {
        UserLog::log(
            action: 'restore',
            modelType: 'Post',
            modelId: $post->post_id,
            description: "Post geri yüklendi: {$post->title}",
            newValues: $post->toArray(),
            ipAddress: $this->getRequestIp(),
            userAgent: $this->getRequestUserAgent(),
            url: $this->getRequestUrl(),
            method: $this->getRequestMethod()
        );

        // Sync position when post is restored
        $this->syncPostPosition($post);
    }

    /**
     * Sync post position with featured items
     */
    private function syncPostPosition(Post $post)
    {
        // Map post positions to zones
        $positionZoneMap = [
            'manşet' => 'manset',
            'sürmanşet' => 'surmanset',
            'öne çıkanlar' => 'one_cikanlar',
        ];

        // Remove from all zones first
        $this->featuredService->unpin('manset', 'post', $post->post_id);
        $this->featuredService->unpin('surmanset', 'post', $post->post_id);
        $this->featuredService->unpin('one_cikanlar', 'post', $post->post_id);

        // Add to appropriate zone if position is not 'normal'
        if ($post->post_position !== 'normal' && isset($positionZoneMap[$post->post_position])) {
            $zone = $positionZoneMap[$post->post_position];

            // Only add if post is published
            if ($post->status === 'published' && $post->published_date && $post->published_date <= now()) {
                $this->featuredService->upsert(
                    $zone,
                    'post',
                    $post->post_id,
                    null, // slot - will be assigned later
                    $post->post_order ?? 0, // priority
                    null, // starts_at
                    null  // ends_at
                );
            }
        }
    }
}
