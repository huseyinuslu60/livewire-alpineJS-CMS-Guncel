<?php

namespace Modules\Articles\Observers;

use App\Traits\HandlesRequestContext;
use Modules\Articles\Models\Article;
use Modules\Headline\Services\FeaturedService;
use Modules\Logs\Models\UserLog;

class ArticleObserver
{
    use HandlesRequestContext;

    protected $featuredService;

    public function __construct(FeaturedService $featuredService)
    {
        $this->featuredService = $featuredService;
    }

    /**
     * Handle the Article "created" event.
     */
    public function created(Article $article): void
    {
        UserLog::log(
            action: 'create',
            modelType: 'Article',
            modelId: $article->article_id,
            description: "Yeni makale oluşturuldu: {$article->title}",
            newValues: $article->toArray(),
            ipAddress: $this->getRequestIp(),
            userAgent: $this->getRequestUserAgent(),
            url: $this->getRequestUrl(),
            method: $this->getRequestMethod()
        );

        $this->syncArticleFeatured($article);
    }

    /**
     * Handle the Article "updated" event.
     */
    public function updated(Article $article): void
    {
        $changes = $article->getChanges();
        $original = $article->getOriginal();

        if (! empty($changes)) {
            UserLog::log(
                action: 'update',
                modelType: 'Article',
                modelId: $article->article_id,
                description: "Makale güncellendi: {$article->title}",
                oldValues: array_intersect_key($original, $changes),
                newValues: $changes,
                ipAddress: $this->getRequestIp(),
                userAgent: $this->getRequestUserAgent(),
                url: $this->getRequestUrl(),
                method: $this->getRequestMethod()
            );
        }

        $this->syncArticleFeatured($article);
    }

    /**
     * Handle the Article "deleted" event.
     */
    public function deleted(Article $article): void
    {
        UserLog::log(
            action: 'delete',
            modelType: 'Article',
            modelId: $article->article_id,
            description: "Makale silindi: {$article->title}",
            oldValues: $article->toArray(),
            ipAddress: $this->getRequestIp(),
            userAgent: $this->getRequestUserAgent(),
            url: $this->getRequestUrl(),
            method: $this->getRequestMethod()
        );

        // Remove from all zones when article is deleted
        $this->featuredService->unpin('manset', 'article', $article->article_id);
        $this->featuredService->unpin('surmanset', 'article', $article->article_id);
        $this->featuredService->unpin('one_cikanlar', 'article', $article->article_id);
    }

    /**
     * Handle the Article "restored" event.
     */
    public function restored(Article $article): void
    {
        UserLog::log(
            action: 'restore',
            modelType: 'Article',
            modelId: $article->article_id,
            description: "Makale geri yüklendi: {$article->title}",
            newValues: $article->toArray(),
            ipAddress: $this->getRequestIp(),
            userAgent: $this->getRequestUserAgent(),
            url: $this->getRequestUrl(),
            method: $this->getRequestMethod()
        );

        $this->syncArticleFeatured($article);
    }

    /**
     * Sync article with featured items based on show_on_mainpage
     */
    private function syncArticleFeatured(Article $article)
    {
        // Remove from all zones first
        $this->featuredService->unpin('manset', 'article', $article->article_id);
        $this->featuredService->unpin('surmanset', 'article', $article->article_id);
        $this->featuredService->unpin('one_cikanlar', 'article', $article->article_id);

        // Add to one_cikanlar zone if show_on_mainpage is true and article is published
        if ($article->show_on_mainpage && $article->status === 'published' && $article->published_at && $article->published_at <= now()) {
            $this->featuredService->upsert(
                'one_cikanlar',
                'article',
                $article->article_id,
                null, // slot - will be assigned later
                0, // priority
                null, // starts_at
                null  // ends_at
            );
        }
    }
}
