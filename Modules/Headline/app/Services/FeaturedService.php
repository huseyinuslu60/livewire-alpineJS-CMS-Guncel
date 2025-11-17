<?php

namespace Modules\Headline\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Headline\Domain\Services\FeaturedValidator;
use Modules\Headline\app\Models\Featured;

class FeaturedService
{
    protected FeaturedValidator $featuredValidator;

    public function __construct(?FeaturedValidator $featuredValidator = null)
    {
        $this->featuredValidator = $featuredValidator ?? app(FeaturedValidator::class);
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
        // Validate featured data
        $this->featuredValidator->validate($zone, $subjectType, $subjectId, $startsAt, $endsAt);

        return Featured::updateOrCreate(
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
    }

    /**
     * Unpin an item from a zone
     */
    public function unpin(string $zone, string $subjectType, int $subjectId): bool
    {
        $deleted = Featured::where('zone', $zone)
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->delete();

        if ($deleted) {
            $this->bustZoneCache($zone);
        }

        return $deleted > 0;
    }

    /**
     * Reorder items in a zone
     */
    public function reorder(string $zone, array $ordered): bool
    {
        try {
            DB::beginTransaction();

            // Clear existing slots for this zone
            Featured::where('zone', $zone)
                ->update(['slot' => null]);

            // Set new slots
            foreach ($ordered as $index => $item) {
                Featured::where('zone', $zone)
                    ->where('subject_type', $item['subject_type'])
                    ->where('subject_id', $item['subject_id'])
                    ->update(['slot' => $index + 1]);
            }

            DB::commit();
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
        $excludeIds = Featured::where('zone', $zone)
            ->pluck('subject_id')
            ->toArray();

        $offset = ($page - 1) * $limit;
        $suggestions = collect();

        if ($type === 'post' || $type === 'all') {
            // Sadece manşet, sürmanşet veya öne çıkan pozisyonundaki haberleri öner
            $postQueryBuilder = \Modules\Posts\Models\Post::published()
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

            $postQuery = $postQueryBuilder
                ->latest('published_date')
                ->offset($offset)
                ->limit($limit)
                ->get()
                ->map(function ($post) {
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
            $articleQueryBuilder = \Modules\Articles\Models\Article::where('status', 'published')
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

            $articleQuery = $articleQueryBuilder
                ->latest('published_at')
                ->offset($offset)
                ->limit($limit)
                ->get()
                ->map(function ($article) {
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
        // If no slot is specified, add to the beginning (slot 1)
        if ($slot === null) {
            // Get the highest slot number in this zone
            $maxSlot = Featured::where('zone', $zone)
                ->whereNotNull('slot')
                ->max('slot') ?? 0;

            // Shift existing items down by 1, starting from the highest slot
            for ($i = $maxSlot; $i >= 1; $i--) {
                Featured::where('zone', $zone)
                    ->where('slot', $i)
                    ->update(['slot' => $i + 1]);
            }

            $slot = 1;
        }

        $item = $this->upsert($zone, $subjectType, $subjectId, $slot);
        $this->bustZoneCache($zone);

        return $item;
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
}
