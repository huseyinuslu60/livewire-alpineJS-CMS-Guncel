<?php

namespace Modules\Headline\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property string $zone
 * @property string $subject_type
 * @property int $subject_id
 * @property int|null $slot
 * @property int $priority
 * @property string|null $starts_at
 * @property string|null $ends_at
 * @property bool $is_active
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class FeaturedItem extends Model
{
    protected $fillable = [
        'zone',
        'subject_type',
        'subject_id',
        'slot',
        'priority',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
        'slot' => 'integer',
        'priority' => 'integer',
    ];

    // Zone constants
    public const ZONES = [
        'manset' => 'Manşet',
        'surmanset' => 'Sürmanşet',
        'one_cikanlar' => 'Öne Çıkanlar',
    ];

    // Subject type constants
    public const SUBJECT_TYPES = [
        'post' => 'Post',
        'article' => 'Article',
    ];

    /**
     * Get the subject model (polymorphic)
     */
    public function subject(): MorphTo
    {
        return $this->morphTo('subject');
    }

    /**
     * Scope: Active items
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }

    /**
     * Scope: By zone
     */
    public function scopeByZone(Builder $query, string $zone): Builder
    {
        return $query->where('zone', $zone);
    }

    /**
     * Scope: With slot (pinned and slotted)
     */
    public function scopeWithSlot(Builder $query): Builder
    {
        return $query->whereNotNull('slot');
    }

    /**
     * Scope: Without slot (pinned but not slotted)
     */
    public function scopeWithoutSlot(Builder $query): Builder
    {
        return $query->whereNull('slot');
    }

    /**
     * Scope: Ordered by slot
     */
    public function scopeOrderedBySlot(Builder $query): Builder
    {
        return $query->orderBy('slot', 'asc');
    }

    /**
     * Check if item is currently active (within time range)
     */
    public function isCurrentlyActive(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $this->starts_at > $now) {
            return false;
        }

        if ($this->ends_at && $this->ends_at < $now) {
            return false;
        }

        return true;
    }

    /**
     * Get zone label
     */
    public function getZoneLabelAttribute(): string
    {
        return self::ZONES[$this->zone] ?? $this->zone;
    }

    /**
     * Get subject type label
     */
    public function getSubjectTypeLabelAttribute(): string
    {
        return self::SUBJECT_TYPES[$this->subject_type] ?? $this->subject_type;
    }
}
