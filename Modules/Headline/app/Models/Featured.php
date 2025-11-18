<?php

namespace Modules\Headline\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property string $zone
 * @property string $subject_type
 * @property int $subject_id
 * @property int|null $slot
 * @property string|null $starts_at
 * @property string|null $ends_at
 * @property bool $is_active
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Featured extends Model
{
    protected $table = 'featured_items';

    protected $fillable = [
        'zone',
        'subject_type',
        'subject_id',
        'slot',
        'starts_at',
        'ends_at',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the subject (morphTo relationship)
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope to filter by zone
     */
    public function scopeByZone($query, string $zone)
    {
        return $query->where('zone', $zone);
    }

    /**
     * Scope to filter by subject type
     */
    public function scopeBySubjectType($query, string $type)
    {
        return $query->where('subject_type', $type);
    }

    /**
     * Scope to order by slot
     */
    public function scopeOrderedBySlot($query)
    {
        return $query->orderBy('slot', 'asc')->orderBy('created_at', 'asc');
    }

    /**
     * Scope to filter active items (not expired and started)
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            // Başlangıç tarihi yoksa veya geçmişte ise
            $q->where(function ($subQ) {
                $subQ->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            });
        })->where(function ($q) {
            // Bitiş tarihi yoksa veya gelecekte ise
            $q->whereNull('ends_at')
                ->orWhere('ends_at', '>', now());
        });
    }

    /**
     * Scope to filter by date range
     */
    public function scopeInDateRange($query, $start, $end)
    {
        return $query->where(function ($q) use ($start, $end) {
            $q->where(function ($subQ) {
                $subQ->whereNull('starts_at')
                    ->whereNull('ends_at');
            })->orWhere(function ($subQ) use ($start, $end) {
                $subQ->where('starts_at', '<=', $end)
                    ->where(function ($subSubQ) use ($start) {
                        $subSubQ->whereNull('ends_at')
                            ->orWhere('ends_at', '>=', $start);
                    });
            });
        });
    }
}
