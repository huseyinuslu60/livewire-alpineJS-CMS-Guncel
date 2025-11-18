<?php

namespace Modules\Logs\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * @property int $log_id
 * @property int $user_id
 * @property string $action
 * @property string|null $model_type
 * @property int|null $model_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property array|null $old_values
 * @property array|null $new_values
 * @property string|null $description
 * @property string|null $url
 * @property string|null $method
 * @property array|null $metadata
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|UserLog byAction($action)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLog byUser($userId)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLog byModel($modelType, $modelId = null)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLog byDateRange($startDate, $endDate)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLog search(?string $term)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLog ofAction($action)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLog ofUser($userId)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLog sortedLatest($column = 'created_at')
 */
class UserLog extends Model
{
    use HasFactory;

    protected $table = 'user_logs';

    protected $primaryKey = 'log_id';

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'ip_address',
        'user_agent',
        'old_values',
        'new_values',
        'description',
        'url',
        'method',
        'metadata',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
    ];

    // Constants for actions
    public const ACTIONS = [
        'create' => 'Oluşturma',
        'update' => 'Güncelleme',
        'delete' => 'Silme',
        'login' => 'Giriş',
        'logout' => 'Çıkış',
        'view' => 'Görüntüleme',
        'export' => 'Dışa Aktarma',
        'import' => 'İçe Aktarma',
        'force_delete' => 'Kalıcı Silme',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    // Scopes
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByModel($query, $modelType, $modelId = null)
    {
        $query = $query->where('model_type', $modelType);

        if ($modelId) {
            $query->where('model_id', $modelId);
        }

        return $query;
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
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
            $q->where('description', $likeOp, "%{$safe}%")
                ->orWhere('action', $likeOp, "%{$safe}%")
                ->orWhere('model_type', $likeOp, "%{$safe}%");
        });
    }

    public function scopeOfAction($query, $action)
    {
        return (is_null($action) || $action === '') ? $query : $query->where('action', $action);
    }

    public function scopeOfUser($query, $userId)
    {
        return (is_null($userId) || $userId === '') ? $query : $query->where('user_id', $userId);
    }

    public function scopeSortedLatest($query, $column = 'created_at')
    {
        return $query->latest($column);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Accessors
    public function getActionLabelAttribute()
    {
        return self::ACTIONS[$this->action] ?? ucfirst($this->action);
    }

    public function getFormattedCreatedAtAttribute()
    {
        return \Carbon\Carbon::parse($this->created_at)->format('d.m.Y H:i:s');
    }

    public function getShortDescriptionAttribute()
    {
        return \Str::limit($this->description, 100);
    }

    // Static methods for logging
    public static function log(
        $action,
        $description = null,
        $modelType = null,
        $modelId = null,
        $oldValues = null,
        $newValues = null,
        $ipAddress = null,
        $userAgent = null,
        $url = null,
        $method = null,
        $metadata = null
    ) {
        $user = Auth::user();
        $request = request();

        return self::create([
            'user_id' => $user instanceof \App\Models\User ? $user->id : null,
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'ip_address' => $ipAddress ?? $request->ip(),
            'user_agent' => $userAgent ?? $request->userAgent(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $description,
            'url' => $url ?? $request->fullUrl(),
            'method' => $method ?? $request->method(),
            'metadata' => $metadata,
        ]);
    }

    public static function logModelChange($model, $action, $description = null, $oldValues = null, $newValues = null)
    {
        return self::log(
            $action,
            $description ?: "Model {$action} işlemi gerçekleştirildi",
            $model,
            $oldValues,
            $newValues
        );
    }

    public static function logUserActivity($action, $description = null, $metadata = null)
    {
        return self::log($action, $description, null, null, null, $metadata);
    }

    // Helper methods
    public function getChangesSummary()
    {
        if (! $this->old_values || ! $this->new_values) {
            return null;
        }

        $changes = [];
        foreach ($this->new_values as $key => $newValue) {
            $oldValue = $this->old_values[$key] ?? null;
            if ($oldValue !== $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
    }

    public function hasLogChanges()
    {
        return ! empty($this->getChangesSummary());
    }
}
