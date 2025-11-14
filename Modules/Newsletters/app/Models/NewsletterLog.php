<?php

namespace Modules\Newsletters\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $record_id
 * @property int $newsletter_id
 * @property int $user_id
 * @property int|null $post_id
 * @property string|null $link
 * @property string $type
 * @property string $status
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class NewsletterLog extends Model
{
    protected $table = 'newsletter_logs';

    protected $primaryKey = 'record_id';

    protected $fillable = [
        'newsletter_id',
        'user_id',
        'post_id',
        'link',
        'type',
        'status',
    ];

    public function newsletter(): BelongsTo
    {
        return $this->belongsTo(Newsletter::class, 'newsletter_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(NewsletterUser::class, 'user_id');
    }

    public function getTypeBadgeAttribute()
    {
        return match ($this->type) {
            'click' => 'bg-blue-100 text-blue-800',
            'open' => 'bg-green-100 text-green-800',
            'bounce' => 'bg-red-100 text-red-800',
            'unsubscribe' => 'bg-orange-100 text-orange-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            'success' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        $safe = str_replace(['%', '_'], ['\\%', '\\_'], $term);
        $driver = $query->getModel()->getConnection()->getDriverName();
        $likeOp = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';

        return $query->where(function ($q) use ($safe, $likeOp) {
            $q->where('link', $likeOp, "%{$safe}%")
                ->orWhereHas('user', function ($subQ) use ($safe, $likeOp) {
                    $subQ->where('name', $likeOp, "%{$safe}%")
                        ->orWhere('email', $likeOp, "%{$safe}%");
                })
                ->orWhereHas('newsletter', function ($subQ) use ($safe, $likeOp) {
                    $subQ->where('name', $likeOp, "%{$safe}%");
                });
        });
    }

    public function scopeOfType($query, $type)
    {
        return (is_null($type) || $type === '') ? $query : $query->where('type', $type);
    }

    public function scopeOfStatus($query, $status)
    {
        return (is_null($status) || $status === '') ? $query : $query->where('status', $status);
    }

    public function scopeSortedLatest($query, $column = 'created_at')
    {
        return $query->latest($column);
    }
}
