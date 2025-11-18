<?php

namespace Modules\Comments\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $comment_id
 * @property int $post_id
 * @property string $name
 * @property string $comment_text
 * @property string $status
 * @property string|null $ip_address
 * @property int $up_vote
 * @property int $down_vote
 * @property string|null $email
 * @property int|null $parent_id
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Modules\Posts\Models\Post $post
 * @property-read \Modules\Comments\Models\Comment|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Comments\Models\Comment> $replies
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Comment ofStatus($status)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment sortedLatest($column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder|Comment search(?string $term)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment approved()
 * @method static \Illuminate\Database\Eloquent\Builder|Comment pending()
 * @method static \Illuminate\Database\Eloquent\Builder|Comment rejected()
 */
class Comment extends Model
{
    use SoftDeletes;

    protected static function boot()
    {
        parent::boot();

        // Observer Service Provider'da kayıtlı - çift kayıt önlemek için kaldırıldı
        // static::observe(\Modules\Comments\Observers\CommentObserver::class);
    }

    protected $primaryKey = 'comment_id';

    protected $fillable = [
        'post_id',
        'name',
        'comment_text',
        'status',
        'ip_address',
        'up_vote',
        'down_vote',
        'email',
        'parent_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // İlişkiler
    public function post()
    {
        return $this->belongsTo(\Modules\Posts\Models\Post::class, 'post_id', 'post_id');
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id', 'comment_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id', 'comment_id');
    }

    // Scope'lar
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
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
            $q->where('name', $likeOp, "%{$safe}%")
                ->orWhere('comment_text', $likeOp, "%{$safe}%")
                ->orWhere('email', $likeOp, "%{$safe}%");
        });
    }

    public function scopeOfStatus($query, $status)
    {
        return (is_null($status) || $status === '') ? $query : $query->where('status', $status);
    }

    public function scopeSortedLatest($query, $column = 'created_at')
    {
        return $query->latest($column);
    }

    // Accessor'lar
    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            'approved' => 'success',
            'pending' => 'warning',
            'rejected' => 'danger',
            default => 'secondary'
        };
    }

    public function getStatusTextAttribute()
    {
        return match ($this->status) {
            'approved' => 'Onaylandı',
            'pending' => 'Beklemede',
            'rejected' => 'Reddedildi',
            default => 'Bilinmiyor'
        };
    }
}
