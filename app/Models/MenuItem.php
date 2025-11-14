<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $title
 * @property string|null $icon
 * @property string $type
 * @property string|null $route
 * @property string|null $module
 * @property string|null $permission
 * @property array|null $roles
 * @property string|null $active_pattern
 * @property int|null $parent_id
 * @property int $sort_order
 * @property bool $is_active
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MenuItem> $children
 * @property-read \App\Models\MenuItem|null $parent
 */
class MenuItem extends Model
{
    protected $fillable = [
        'name',
        'title',
        'icon',
        'type',
        'route',
        'module',
        'permission',
        'roles',
        'active_pattern',
        'parent_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'roles' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Alt menü item'ları
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Üst menü item'ı
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * Aktif menü item'ları getir
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Ana menü item'ları getir (parent_id null olanlar)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Kullanıcının erişebileceği menü item'ları getir
     * Artık permission tabanlı sistem kullandığımız için roles kontrolü yapmıyoruz
     */
    public function scopeForUser($query, $user)
    {
        // Permission tabanlı sistem kullandığımız için tüm aktif menü item'larını döndür
        // Permission kontrolü MenuHelper'da yapılıyor
        return $query;
    }

    /**
     * Menü item'ının aktif olup olmadığını kontrol et
     */
    public function isActive()
    {
        if (! $this->active_pattern) {
            return false;
        }

        $patterns = explode('|', $this->active_pattern);
        foreach ($patterns as $pattern) {
            if (request()->routeIs(trim($pattern))) {
                return true;
            }
        }

        return false;
    }
}
