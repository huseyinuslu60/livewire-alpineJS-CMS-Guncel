<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * @property int $id
     * @property string $name
     * @property string $email
     * @property string|null $password
     * @property string|null $last_login_at
     * @property string|null $table_columns
     * @property string|null $email_verified_at
     * @property string|null $remember_token
     * @property string|null $created_at
     * @property string|null $updated_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
     *
     * @method static \Illuminate\Database\Eloquent\Builder|User search(?string $term)
     * @method static \Illuminate\Database\Eloquent\Builder|User sortedLatest($column = 'created_at')
     */

    /**
     * Toplu atanabilir özellikler.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'last_login_at',
        'table_columns',
    ];

    /**
     * Serialization için gizlenmesi gereken özellikler.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Cast edilmesi gereken özellikler.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // Laravel 12'de Auth::attempt() ile uyumlu
            'table_columns' => 'array',
        ];
    }

    /**
     * Kullanıcının yazar profili.
     */
    public function authorProfile(): HasOne
    {
        return $this->hasOne(\Modules\Authors\Models\Author::class);
    }

    /**
     * Kullanıcının makaleleri.
     */
    public function articles(): HasMany
    {
        return $this->hasMany(\Modules\Articles\Models\Article::class, 'author_id');
    }

    /**
     * Kullanıcının oluşturduğu yazılar.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(\Modules\Posts\Models\Post::class, 'created_by');
    }

    /**
     * Kullanıcıları isim veya e-posta ile ara.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
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

    /**
     * Kullanıcıları belirtilen sütuna göre en yeniye göre sırala.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $column
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortedLatest($query, $column = 'created_at')
    {
        return $query->latest($column);
    }
}
