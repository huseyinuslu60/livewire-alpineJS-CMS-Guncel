<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'version',
        'is_active',
        'icon',
        'route_prefix',
        'permissions',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'permissions' => 'array',
    ];

    /**
     * Aktif modülleri getir
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Modülü aktif/pasif yap
     */
    public function toggleStatus()
    {
        $this->update(['is_active' => ! $this->is_active]);

        return $this;
    }

    /**
     * Modülün aktif olup olmadığını kontrol et
     */
    public static function isActive($moduleName)
    {
        return self::where('name', $moduleName)->where('is_active', true)->exists();
    }
}
