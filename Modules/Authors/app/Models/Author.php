<?php

namespace Modules\Authors\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $author_id
 * @property int $user_id
 * @property string $title
 * @property string|null $bio
 * @property string|null $twitter
 * @property string|null $linkedin
 * @property string|null $facebook
 * @property string|null $instagram
 * @property string|null $website
 * @property bool $show_on_mainpage
 * @property int $weight
 * @property string $status
 * @property string|null $image
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Author ofStatus($status)
 * @method static \Illuminate\Database\Eloquent\Builder|Author sortedLatest($column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder|Author search(?string $term)
 */
class Author extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'bio',
        'image',
        'twitter',
        'linkedin',
        'facebook',
        'instagram',
        'website',
        'show_on_mainpage',
        'weight',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'show_on_mainpage' => 'boolean',
            'status' => 'boolean',
            'weight' => 'integer',
        ];
    }

    /**
     * Get the user that owns the author profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get the articles for the author profile.
     */
    public function articles(): HasMany
    {
        return $this->hasMany(\Modules\Articles\Models\Article::class, 'author_id', 'user_id');
    }

    /**
     * Check if the author profile is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' || $this->status === true;
    }

    /**
     * Get the author's display name.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->user->name ?? $this->title;
    }

    /**
     * Get the author's title or default.
     */
    public function getTitleOrDefaultAttribute(): string
    {
        return $this->title ?: 'Yazar';
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

        return $query->whereHas('user', function ($q) use ($safe, $likeOp) {
            $q->where(function ($subQ) use ($safe, $likeOp) {
                $subQ->where('name', $likeOp, "%{$safe}%")
                    ->orWhere('email', $likeOp, "%{$safe}%");
            });
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

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Modules\Authors\Database\Factories\AuthorFactory::new();
    }
}
