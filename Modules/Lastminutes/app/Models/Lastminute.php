<?php

namespace Modules\Lastminutes\Models;

use App\Traits\AuditFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $lastminute_id
 * @property string $title
 * @property string $redirect
 * @property string|null $end_at
 * @property string $status
 * @property int $weight
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 */
class Lastminute extends Model
{
    use AuditFields, HasFactory, SoftDeletes;

    protected $table = 'lastminutes';

    protected $primaryKey = 'lastminute_id';

    // Constants
    public const STATUSES = ['active', 'inactive', 'expired'];

    public const STATUS_LABELS = [
        'active' => 'Aktif',
        'inactive' => 'Pasif',
        'expired' => 'Süresi Dolmuş',
    ];

    protected $fillable = [
        'title',
        'redirect',
        'end_at',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
        'weight',
    ];

    protected $casts = [
        'end_at' => 'datetime',
        'weight' => 'integer',
    ];

    protected $dates = [
        'end_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'deleted_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('end_at')
                ->orWhere('end_at', '>', now());
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('weight', 'asc')
            ->latest('created_at');
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
            $q->where('title', $likeOp, "%{$safe}%");
        });
    }

    public function scopeOfStatus($query, $status)
    {
        return (is_null($status) || $status === '' || $status === 'all') ? $query : $query->where('status', $status);
    }

    public function scopeSortedLatest($query, $column = 'created_at')
    {
        return $query->latest($column);
    }

    // Accessors & Mutators
    public function getIsExpiredAttribute()
    {
        return $this->end_at && $this->end_at < now();
    }

    public function getStatusLabelAttribute()
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getFormattedEndAtAttribute()
    {
        return $this->end_at ? \Carbon\Carbon::parse($this->end_at)->format('d.m.Y H:i') : null;
    }

    public function getRedirectUrlAttribute()
    {
        if ($this->redirect) {
            // Eğer URL http/https ile başlamıyorsa, http:// ekle
            if (! preg_match('/^https?:\/\//', $this->redirect)) {
                return 'http://'.$this->redirect;
            }

            return $this->redirect;
        }

        return null;
    }

    // Methods
    public function isActive()
    {
        return $this->status === 'active' && ! $this->is_expired;
    }

    public function markAsExpired()
    {
        $this->update(['status' => 'expired']);
    }

    public function activate()
    {
        $this->update(['status' => 'active']);
    }

    public function deactivate()
    {
        $this->update(['status' => 'inactive']);
    }
}
