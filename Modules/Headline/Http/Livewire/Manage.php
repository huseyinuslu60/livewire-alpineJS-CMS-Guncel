<?php

namespace Modules\Headline\Http\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Modules\Headline\Services\FeaturedService;

class Manage extends Component
{
    protected FeaturedService $featuredService;

    public function boot()
    {
        $this->featuredService = app(FeaturedService::class);
    }

    // Public properties
    public string $activeZone = 'manset';

    public string $successMessage = '';

    public array $zones = [
        'manset' => 'Manşet',
        'surmanset' => 'Sürmanşet',
        'one_cikanlar' => 'Öne Çıkanlar',
    ];

    public array $zoneSlotLimits = [
        'manset' => 15,
        'surmanset' => 3,
        'one_cikanlar' => 10,
    ];

    public string $query = '';

    public string $type = 'all';

    public int $suggestLimit = 15;

    public int $suggestPage = 1;

    public bool $hasMoreSuggestions = false;

    // Schedule modal properties
    public bool $showScheduleModal = false;

    public string $schedZone = '';

    public string $schedSubjectType = '';

    public int $schedSubjectId = 0;

    public ?string $schedStartsAt = null;

    public ?string $schedEndsAt = null;

    // Data properties
    /** @var array<string, \Illuminate\Database\Eloquent\Collection<int, \Modules\Headline\app\Models\Featured>> */
    public $pinnedByZone = [];

    /** @var \Illuminate\Support\Collection<int, \Modules\Posts\Models\Post> */
    public \Illuminate\Support\Collection $suggestions;

    protected $listeners = [
        'refreshComponent' => '$refresh',
        'pinned' => 'handlePinned',
        'unpinned' => 'handleUnpinned',
        'reordered' => 'handleReordered',
    ];

    public function mount()
    {
        Gate::authorize('view featured');
        $this->suggestions = collect();
        $this->loadData();
    }

    public function render()
    {
        $this->loadData();

        /** @var view-string $view */
        $view = 'headline::livewire.manage';

        return view($view)
            ->extends('layouts.admin')->section('content');
    }

    /**
     * Set active zone
     */
    public function setZone(string $zone)
    {
        if (array_key_exists($zone, $this->zones)) {
            $this->activeZone = $zone;
            $this->loadSuggestions();
        }
    }

    /**
     * Pin an item to a zone
     */
    public function pin(string $zone, string $subjectType, int $subjectId, ?int $slot = null)
    {
        Gate::authorize('view featured');

        $service = app(FeaturedService::class);
        $service->pin($zone, $subjectType, $subjectId, $slot);

        $this->dispatch('pinned', [
            'zone' => $zone,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
        ]);

        $this->loadData();
    }

    /**
     * Unpin an item from a zone
     */
    public function unpin(string $zone, string $subjectType, int $subjectId)
    {
        Gate::authorize('view featured');

        $service = app(FeaturedService::class);
        $service->unpin($zone, $subjectType, $subjectId);

        $this->dispatch('unpinned', [
            'zone' => $zone,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
        ]);

        $this->loadData();
    }

    /**
     * Save order for a zone
     */
    public function saveOrder(string $zone, array $ordered = [])
    {
        Gate::authorize('view featured');

        // If no ordered array provided, get current order from DOM
        if (empty($ordered)) {
            /** @var \Illuminate\Support\Collection<int, \Modules\Headline\app\Models\Featured> $pinnedItems */
            $pinnedItems = $this->pinnedByZone[$zone];
            $ordered = $pinnedItems->map(function (\Modules\Headline\app\Models\Featured $item) {
                return [
                    'subject_type' => $item->subject_type,
                    'subject_id' => $item->subject_id,
                ];
            })->toArray();
        }

        $service = app(FeaturedService::class);
        $result = $service->reorder($zone, $ordered);

        // Set success message for Livewire
        $this->successMessage = 'Sıralama başarıyla kaydedildi!';

        $this->dispatch('reordered', [
            'zone' => $zone,
            'count' => count($ordered),
        ]);

        $this->loadData();
    }

    /**
     * Open schedule modal
     */
    public function openSchedule(string $zone, string $subjectType, int $subjectId)
    {
        $this->schedZone = $zone;
        $this->schedSubjectType = $subjectType;
        $this->schedSubjectId = $subjectId;

        // Mevcut zamanlama bilgilerini yükle
        $featured = $this->featuredService->getQuery()
            ->where('zone', $zone)
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->first();

        if ($featured) {
            /** @var \Modules\Headline\app\Models\Featured $featured */
            /** @var \Carbon\Carbon|\DateTime|string|null $startsAt */
            $startsAt = $featured->starts_at;
            /** @var \Carbon\Carbon|\DateTime|string|null $endsAt */
            $endsAt = $featured->ends_at;

            $this->schedStartsAt = ($startsAt !== null) ?
                (is_string($startsAt) ?
                    Carbon::parse($startsAt)->format('Y-m-d\TH:i') :
                    ($startsAt instanceof \DateTimeInterface ? $startsAt->format('Y-m-d\TH:i') : null)) :
                null;
            $this->schedEndsAt = ($endsAt !== null) ?
                (is_string($endsAt) ?
                    Carbon::parse($endsAt)->format('Y-m-d\TH:i') :
                    ($endsAt instanceof \DateTimeInterface ? $endsAt->format('Y-m-d\TH:i') : null)) :
                null;
        } else {
            $this->schedStartsAt = null;
            $this->schedEndsAt = null;
        }

        $this->showScheduleModal = true;
    }

    /**
     * Apply schedule
     */
    public function applySchedule()
    {
        Gate::authorize('view featured');

        $service = app(FeaturedService::class);

        // If no time is specified, add immediately with slot
        if (empty($this->schedStartsAt) && empty($this->schedEndsAt)) {
            $service->pin($this->schedZone, $this->schedSubjectType, $this->schedSubjectId, null);
            $this->successMessage = 'İçerik başarıyla eklendi!';
        } else {
            // For scheduled items, use pinScheduled method (no slot, just priority)
            $service->pinScheduled(
                $this->schedZone,
                $this->schedSubjectType,
                $this->schedSubjectId,
                $this->schedStartsAt ? new \DateTime($this->schedStartsAt) : null,
                $this->schedEndsAt ? new \DateTime($this->schedEndsAt) : null,
                0 // priority
            );
            $this->successMessage = 'Zamanlama başarıyla kaydedildi! Zaman geldiğinde otomatik olarak en üste çıkacak.';
        }

        $this->showScheduleModal = false;
        $this->loadData();
    }

    /**
     * Clear schedule (reset times)
     */
    public function clearSchedule()
    {
        Gate::authorize('view featured');

        $service = app(FeaturedService::class);
        $service->upsert(
            $this->schedZone,
            $this->schedSubjectType,
            $this->schedSubjectId,
            null, // slot
            null, // priority
            null, // starts_at
            null  // ends_at
        );

        $this->schedStartsAt = null;
        $this->schedEndsAt = null;
        $this->successMessage = 'Zamanlama başarıyla sıfırlandı!';
        $this->loadData();
    }

    /**
     * Move item to another zone
     */
    public function moveToZone(string $from, string $to, string $subjectType, int $subjectId)
    {
        Gate::authorize('view featured');

        $service = app(FeaturedService::class);
        $service->moveToZone($from, $to, $subjectType, $subjectId);

        $this->loadData();
    }

    /**
     * Load data for current zone
     */
    private function loadData()
    {
        $service = app(FeaturedService::class);

        // Load pinned items for all zones
        $this->pinnedByZone = [];
        foreach ($this->zones as $zone => $label) {
            $this->pinnedByZone[$zone] = $service->getPinnedForZone($zone);
        }

        // Load suggestions for active zone
        $this->loadSuggestions();
    }

    /**
     * Load suggestions for active zone
     */
    private function loadSuggestions()
    {
        $service = app(FeaturedService::class);
        $this->suggestions = $service->getSuggestions(
            $this->activeZone,
            $this->type,
            $this->query,
            $this->suggestLimit,
            $this->suggestPage
        );

        // Check if there are more suggestions
        $this->hasMoreSuggestions = $this->suggestions->count() >= $this->suggestLimit;
    }

    /**
     * Load more suggestions
     */
    public function loadMoreSuggestions()
    {
        $this->suggestPage++;
        $service = app(FeaturedService::class);
        $newSuggestions = $service->getSuggestions(
            $this->activeZone,
            $this->type,
            $this->query,
            $this->suggestLimit,
            $this->suggestPage
        );

        $this->suggestions = $this->suggestions->merge($newSuggestions);
        $this->hasMoreSuggestions = $newSuggestions->count() >= $this->suggestLimit;
    }

    /**
     * Updated query - reload suggestions
     */
    public function updatedQuery()
    {
        $this->loadSuggestions();
    }

    /**
     * Updated type - reload suggestions
     */
    public function updatedType()
    {
        $this->loadSuggestions();
    }

    /**
     * Handle pinned event
     */
    public function handlePinned($data)
    {
        // Could add toast notification here
    }

    /**
     * Handle unpinned event
     */
    public function handleUnpinned($data)
    {
        // Could add toast notification here
    }

    /**
     * Handle reordered event
     */
    public function handleReordered($data)
    {
        // Could add toast notification here
    }
}
