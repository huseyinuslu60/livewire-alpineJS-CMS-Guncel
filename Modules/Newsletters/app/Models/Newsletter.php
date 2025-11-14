<?php

namespace Modules\Newsletters\Models;

use App\Models\User;
use App\Traits\AuditFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $newsletter_id
 * @property string $name
 * @property string $status
 * @property string $mail_status
 * @property string|null $mail_subject
 * @property string|null $mail_body
 * @property string|null $mail_body_raw
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string|null $started_at
 * @property string|null $completed_at
 * @property int $success_count
 * @property int $total_count
 * @property bool $reklam
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Newsletter extends Model
{
    use AuditFields;

    protected $table = 'newsletters';

    protected $primaryKey = 'newsletter_id';

    protected $fillable = [
        'name',
        'status',
        'mail_status',
        'mail_subject',
        'mail_body',
        'mail_body_raw',
        'created_by',
        'updated_by',
        'started_at',
        'completed_at',
        'success_count',
        'total_count',
        'reklam',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'reklam' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function newsletterPosts(): HasMany
    {
        return $this->hasMany(NewsletterPost::class, 'newsletter_id');
    }

    public function newsletterLogs(): HasMany
    {
        return $this->hasMany(NewsletterLog::class, 'newsletter_id');
    }

    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            'draft' => 'bg-gray-100 text-gray-800',
            'sending' => 'bg-blue-100 text-blue-800',
            'sent' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getMailStatusBadgeAttribute()
    {
        return match ($this->mail_status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'sent' => 'bg-green-100 text-green-800',
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
            $q->where('name', $likeOp, "%{$safe}%")
                ->orWhere('mail_subject', $likeOp, "%{$safe}%");
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
