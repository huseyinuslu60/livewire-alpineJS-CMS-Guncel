<?php

namespace Modules\Newsletters\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $user_id
 * @property string $name
 * @property string $email
 * @property string $status
 * @property string $email_status
 * @property string|null $hash_code
 * @property string $verify_status
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read string $status_badge
 * @property-read string $email_status_badge
 * @property-read string $verify_status_badge
 *
 * @method static \Illuminate\Database\Eloquent\Builder|NewsletterUser ofStatus($status)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsletterUser sortedLatest($column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder|NewsletterUser search(?string $term)
 */
class NewsletterUser extends Model
{
    protected $table = 'newsletter_users';

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'name',
        'email',
        'status',
        'email_status',
        'hash_code',
        'verify_status',
    ];

    public function newsletterLogs(): HasMany
    {
        return $this->hasMany(NewsletterLog::class, 'user_id');
    }

    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            'active' => 'bg-green-100 text-green-800',
            'inactive' => 'bg-gray-100 text-gray-800',
            'unsubscribed' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getEmailStatusBadgeAttribute()
    {
        return match ($this->email_status) {
            'verified' => 'bg-green-100 text-green-800',
            'unverified' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getVerifyStatusBadgeAttribute()
    {
        return match ($this->verify_status) {
            'verified' => 'bg-green-100 text-green-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
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
            $q->where('name', $likeOp, "%{$safe}%")
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
}
