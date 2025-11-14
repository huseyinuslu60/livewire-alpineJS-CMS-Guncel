<?php

namespace Modules\Banks\Models;

use App\Traits\AuditFields;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $question_id
 * @property string $title
 * @property string $name
 * @property string $question
 * @property string|null $answer
 * @property string $status
 * @property string|null $stock
 * @property string|null $email
 * @property int|null $updated_by
 * @property int $hit
 * @property string|null $ip_address
 * @property string|null $answer_title
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class InvestorQuestion extends Model
{
    use AuditFields, HasFactory;

    protected $table = 'investor_questions';

    protected $primaryKey = 'question_id';

    protected $fillable = [
        'title',
        'name',
        'question',
        'answer',
        'status',
        'stock',
        'email',
        'updated_by',
        'hit',
        'ip_address',
        'answer_title',
    ];

    protected $casts = [
        'hit' => 'integer',
    ];

    // Constants
    public const STATUSES = ['pending', 'answered', 'rejected'];

    public const STATUS_LABELS = [
        'pending' => 'Beklemede',
        'answered' => 'Cevaplandı',
        'rejected' => 'Reddedildi',
    ];

    // Relationships
    public function updater(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by', 'id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAnswered($query)
    {
        return $query->where('status', 'answered');
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
            $q->where('title', $likeOp, "%{$safe}%")
                ->orWhere('name', $likeOp, "%{$safe}%")
                ->orWhere('question', $likeOp, "%{$safe}%");
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

    // Accessors
    public function getStatusLabelAttribute()
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function getFormattedCreatedAtAttribute()
    {
        if (! $this->created_at) {
            return null;
        }

        return is_string($this->created_at)
            ? Carbon::parse($this->created_at)->format('d.m.Y H:i')
            : $this->created_at->format('d.m.Y H:i');
    }

    // Methods
    public function markAsAnswered($answer, $answerTitle = null, $userId = null)
    {
        // Audit fields (updated_by) are handled by AuditFields trait
        $this->update([
            'answer' => $answer,
            'answer_title' => $answerTitle,
            'status' => 'answered',
        ]);
    }

    public function markAsRejected($userId = null)
    {
        // Audit fields (updated_by) are handled by AuditFields trait
        $this->update([
            'status' => 'rejected',
        ]);
    }

    public function updateAnswer($answer, $answerTitle = null, $userId = null)
    {
        // Audit fields (updated_by) are handled by AuditFields trait
        $this->update([
            'answer' => $answer,
            'answer_title' => $answerTitle,
        ]);
    }

    public function incrementHit()
    {
        $this->increment('hit');
    }

    public static function getStatusLabels()
    {
        return self::STATUS_LABELS;
    }
}
