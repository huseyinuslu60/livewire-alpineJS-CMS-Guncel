<?php

namespace Modules\Headline\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Articles\Services\ArticleService;
use Modules\Headline\app\Models\Featured;
use Modules\Headline\Domain\Events\FeaturedCreated;
use Modules\Headline\Domain\Events\FeaturedDeleted;
use Modules\Headline\Domain\Events\FeaturedItemsReordered;
use Modules\Headline\Domain\Events\FeaturedUpdated;
use Modules\Headline\Domain\Repositories\FeaturedRepositoryInterface;
use Modules\Headline\Domain\Services\FeaturedValidator;
use Modules\Posts\Services\PostsService;

class FeaturedService
{
    protected FeaturedValidator $featuredValidator;

    protected FeaturedRepositoryInterface $featuredRepository;

    protected PostsService $postsService;

    protected ArticleService $articleService;

    public function __construct(
        ?FeaturedValidator $featuredValidator = null,
        ?FeaturedRepositoryInterface $featuredRepository = null,
        ?PostsService $postsService = null,
        ?ArticleService $articleService = null
    ) {
        $this->featuredValidator = $featuredValidator ?? app(FeaturedValidator::class);
        $this->featuredRepository = $featuredRepository ?? app(FeaturedRepositoryInterface::class);
        $this->postsService = $postsService ?? app(PostsService::class);
        $this->articleService = $articleService ?? app(ArticleService::class);
    }

    /**
     * Upsert a featured item
     */
    public function upsert(
        string $zone,
        string $subjectType,
        int $subjectId,
        ?int $slot = null,
        ?int $priority = null,
        ?\DateTime $startsAt = null,
        ?\DateTime $endsAt = null
    ): Featured {
        try {
            // Validate featured data
            $this->featuredValidator->validate($zone, $subjectType, $subjectId, $startsAt, $endsAt);

            return DB::transaction(function () use ($zone, $subjectType, $subjectId, $slot, $priority, $startsAt, $endsAt) {
                $existing = $this->featuredRepository->findByZoneAndSubject($zone, $subjectType, $subjectId);
                $isNew = $existing === null;

                $featured = $this->featuredRepository->updateOrCreate(
                    [
                        'zone' => $zone,
                        'subject_type' => $subjectType,
                        'subject_id' => $subjectId,
                    ],
                    [
                        'slot' => $slot,
                        'priority' => $priority ?? 0,
                        'starts_at' => $startsAt,
                        'ends_at' => $endsAt,
                        'is_active' => true,
                    ]
                );

                // Fire domain event
                if ($isNew) {
                    Event::dispatch(new FeaturedCreated($featured));
                } else {
                    Event::dispatch(new FeaturedUpdated($featured, ['slot', 'priority', 'starts_at', 'ends_at']));
                }

                return $featured;
            });
        } catch (\Exception $e) {
            \App\Helpers\LogHelper::error('FeaturedService upsert error', [
                'zone' => $zone,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Unpin an item from a zone
     */
    public function unpin(string $zone, string $subjectType, int $subjectId): bool
    {
        try {
            return DB::transaction(function () use ($zone, $subjectType, $subjectId) {
                $featured = $this->featuredRepository->findByZoneAndSubject($zone, $subjectType, $subjectId);

                if (! $featured) {
                    return false;
                }

                $deleted = $this->featuredRepository->deleteByZoneAndSubject($zone, $subjectType, $subjectId);

                if ($deleted > 0) {
                    // Fire domain event
                    Event::dispatch(new FeaturedDeleted($featured));
                    $this->bustZoneCache($zone);
                }

                return $deleted > 0;
            });
        } catch (\Exception $e) {
            \App\Helpers\LogHelper::error('FeaturedService unpin error', [
                'zone' => $zone,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Reorder items in a zone
     */
    public function reorder(string $zone, array $ordered): bool
    {
        try {
            DB::beginTransaction();

            // Clear existing slots for this zone
            $zoneItems = $this->featuredRepository->findByZone($zone);
            /** @var \Modules\Headline\app\Models\Featured $item */
            foreach ($zoneItems as $item) {
                $this->featuredRepository->update($item, ['slot' => null]);
            }

            // Set new slots
            foreach ($ordered as $index => $item) {
                $featured = $this->featuredRepository->findByZoneAndSubject(
                    $zone,
                    $item['subject_type'],
                    $item['subject_id']
                );

                if ($featured) {
                    $this->featuredRepository->update($featured, ['slot' => $index + 1]);
                }
            }

            DB::commit();

            // Fire domain event
            Event::dispatch(new FeaturedItemsReordered($zone, $ordered));

            $this->bustZoneCache($zone);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Move item from one zone to another
     */
    public function moveToZone(
        string $fromZone,
        string $toZone,
        string $subjectType,
        int $subjectId
    ): bool {
        try {
            DB::beginTransaction();

            // Remove from source zone
            $this->unpin($fromZone, $subjectType, $subjectId);

            // Add to target zone (without slot)
            $this->upsert($toZone, $subjectType, $subjectId);

            DB::commit();
            $this->bustZoneCache($fromZone);
            $this->bustZoneCache($toZone);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get featured items for a zone
     */
    public function getForZone(string $zone, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = "headline:v2:{$zone}:{$limit}";

        return Cache::remember($cacheKey, 300, function () use ($zone, $limit) {
            return Featured::with('subject')
                ->active()
                ->byZone($zone)
                ->orderedBySlot()
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get pinned items for a zone (with and without slots)
     */
    public function getPinnedForZone(string $zone): \Illuminate\Database\Eloquent\Collection
    {
        // Get zone slot limit
        $slotLimits = [
            'manset' => 15,
            'surmanset' => 3,
            'one_cikanlar' => 10,
        ];

        $maxSlots = $slotLimits[$zone] ?? 10;

        // Get scheduled items that should be active now (PRIORITY - goes to top)
        $scheduledItems = Featured::with('subject')
            ->where('zone', $zone)
            ->where('is_active', true)
            ->whereNotNull('starts_at')
            ->where('starts_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            })
            ->orderBy('starts_at', 'asc')
            ->get();

        // Get scheduled items that are waiting (not yet active)
        $waitingItems = Featured::with('subject')
            ->where('zone', $zone)
            ->where('is_active', true)
            ->whereNotNull('starts_at')
            ->where('starts_at', '>', now())
            ->orderBy('starts_at', 'asc')
            ->get();

        // Get normal items (with slots) - SECONDARY
        $normalItems = Featured::with('subject')
            ->where('zone', $zone)
            ->where('is_active', true)
            ->whereNotNull('slot')
            ->whereNull('starts_at')
            ->whereNull('ends_at')
            ->orderBy('slot', 'asc')
            ->get();

        // Combine: Active scheduled items FIRST, then normal items, then waiting items
        $allItems = $scheduledItems->concat($normalItems)->concat($waitingItems);

        // If we have more than max slots, keep only the first maxSlots
        if ($allItems->count() > $maxSlots) {
            $allItems = $allItems->take($maxSlots);
        }

        return $allItems;
    }

    /**
     * Get suggestions for a zone (items not already in that zone)
     */
    public function getSuggestions(string $zone, string $type = 'all', string $query = '', int $limit = 15, int $page = 1): \Illuminate\Support\Collection
    {
        // Get exclude IDs (no cache for real-time updates)
        $excludeIds = $this->featuredRepository->getSubjectIdsByZone($zone);

        $offset = ($page - 1) * $limit;
        $suggestions = collect();

        if ($type === 'post' || $type === 'all') {
            // Sadece manşet, sürmanşet veya öne çıkan pozisyonundaki haberleri öner
            /** @var \Illuminate\Database\Eloquent\Builder<\Modules\Posts\Models\Post> $postQueryBuilder */
            $postQueryBuilder = $this->postsService->getQuery()
                ->published()
                ->select('post_id', 'title', 'published_date', 'created_at', 'post_position')
                ->whereNotIn('post_id', $excludeIds)
                ->whereIn('post_position', ['manşet', 'sürmanşet', 'öne çıkanlar']);

            // 0-yutmayan arama: query
            $queryTerm = trim((string) $query);
            if ($queryTerm !== '') {
                $safe = str_replace(['%', '_'], ['\\%', '\\_'], $queryTerm);
                $driver = $postQueryBuilder->getModel()->getConnection()->getDriverName();
                $likeOp = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';
                $postQueryBuilder->where('title', $likeOp, "%{$safe}%");
            }

            /** @var \Illuminate\Database\Eloquent\Collection<int, \Modules\Posts\Models\Post> $postCollection */
            $postCollection = $postQueryBuilder
                ->latest('published_date')
                ->offset($offset)
                ->limit($limit)
                ->get();

            $postQuery = $postCollection->map(function (\Modules\Posts\Models\Post $post) {
                return (object) [
                    'id' => $post->post_id,
                    'type' => 'post',
                    'title' => $post->title,
                    'published_date' => $post->published_date,
                    'post_position' => $post->post_position,
                    'subject' => $post,
                ];
            });

            $suggestions = $suggestions->merge($postQuery);
        }

        if ($type === 'article' || $type === 'all') {
            /** @var \Illuminate\Database\Eloquent\Builder<\Modules\Articles\Models\Article> $articleQueryBuilder */
            $articleQueryBuilder = $this->articleService->getQuery()
                ->where('status', 'published')
                ->select('article_id', 'title', 'published_at', 'created_at')
                ->whereNotIn('article_id', $excludeIds);

            // 0-yutmayan arama: query
            $queryTerm = trim((string) $query);
            if ($queryTerm !== '') {
                $safe = str_replace(['%', '_'], ['\\%', '\\_'], $queryTerm);
                $driver = $articleQueryBuilder->getModel()->getConnection()->getDriverName();
                $likeOp = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';
                $articleQueryBuilder->where('title', $likeOp, "%{$safe}%");
            }

            /** @var \Illuminate\Database\Eloquent\Collection<int, \Modules\Articles\Models\Article> $articleCollection */
            $articleCollection = $articleQueryBuilder
                ->latest('published_at')
                ->offset($offset)
                ->limit($limit)
                ->get();

            $articleQuery = $articleCollection->map(function (\Modules\Articles\Models\Article $article) {
                return (object) [
                    'id' => $article->article_id,
                    'type' => 'article',
                    'title' => $article->title,
                    'published_date' => $article->published_at,
                    'subject' => $article,
                ];
            });

            $suggestions = $suggestions->merge($articleQuery);
        }

        return $suggestions->sortByDesc('published_date')->take($limit);
    }

    /**
     * Bust zone cache
     */
    public function bustZoneCache(string $zone): void
    {
        // Clear getForZone cache
        $limits = [4, 6, 8, 10, 12];

        foreach ($limits as $limit) {
            Cache::forget("headline:v2:{$zone}:{$limit}");
        }

        // Clear exclude IDs cache if it exists
        Cache::forget("headline:exclude:{$zone}");
    }

    /**
     * Pin an item to a zone
     */
    public function pin(
        string $zone,
        string $subjectType,
        int $subjectId,
        ?int $slot = null
    ): Featured {
        try {
            return DB::transaction(function () use ($zone, $subjectType, $subjectId, $slot) {
                // If no slot is specified, add to the beginning (slot 1)
                if ($slot === null) {
                    // Get the highest slot number in this zone
                    $maxSlot = $this->featuredRepository->getMaxSlotForZone($zone) ?? 0;

                    // Shift existing items down by 1, starting from the highest slot
                    for ($i = $maxSlot; $i >= 1; $i--) {
                        $items = $this->featuredRepository->findByZoneAndSlot($zone, $i);
                        /** @var \Modules\Headline\app\Models\Featured $item */
                        foreach ($items as $item) {
                            $this->featuredRepository->update($item, ['slot' => $i + 1]);
                        }
                    }

                    $slot = 1;
                }

                $item = $this->upsert($zone, $subjectType, $subjectId, $slot);
                $this->bustZoneCache($zone);

                return $item;
            });
        } catch (\Exception $e) {
            \App\Helpers\LogHelper::error('FeaturedService pin error', [
                'zone' => $zone,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'slot' => $slot,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Pin a scheduled item (without slot, uses priority)
     */
    public function pinScheduled(
        string $zone,
        string $subjectType,
        int $subjectId,
        ?\DateTime $startsAt = null,
        ?\DateTime $endsAt = null,
        int $priority = 0
    ): Featured {
        // For scheduled items, don't use slots, use priority instead
        return $this->upsert($zone, $subjectType, $subjectId, null, $priority, $startsAt, $endsAt);
    }

    /**
     * Get query builder for featured items
     */
    public function getQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->featuredRepository->getQuery();
    }
}
